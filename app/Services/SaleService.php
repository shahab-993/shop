<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemAllocation;
use App\Models\InventoryBatch;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Create a new sale with its items.
     * Stock is NOT reduced until the sale is completed.
     */
    public function create(array $data, int $userId): Sale
    {
        return DB::transaction(function () use ($data, $userId) {

            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => 'A sale must contain at least one item.',
                ]);
            }

            $sale = Sale::create([
                'user_id' => $userId,
                'invoice_number' => $data['invoice_number'],
                'buyer_name' => $data['buyer_name'] ?? null,
                'buyer_phone' => $data['buyer_phone'] ?? null,
                'sale_date' => $data['sale_date'],
                'subtotal' => '0.00',
                'discount_amount' => '0.00',
                'total_amount' => '0.00',
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotalCents = 0;
            $itemDiscountTotalCents = 0;

            foreach ($items as $item) {

                $product = Product::query()
                    ->whereKey($item['product_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more selected products are unavailable.',
                    ]);
                }

                $quantityMilli = $this->toMinorUnits(
                    $item['quantity'],
                    3
                );

                $unitPriceCents = $this->toMinorUnits(
                    $item['unit_price'],
                    2
                );

                $itemDiscountCents = $this->toMinorUnits(
                    $item['discount_amount'] ?? 0,
                    2
                );

                if (
                    $quantityMilli <= 0 ||
                    $unitPriceCents < 0 ||
                    $itemDiscountCents < 0
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Invalid sale item values.',
                    ]);
                }

                // Calculate gross amount.
                $grossCents = $this->multiplyAndRound(
                    $quantityMilli,
                    $unitPriceCents,
                    1000
                );

                if ($itemDiscountCents > $grossCents) {
                    throw ValidationException::withMessages([
                        'items' => 'Item discount cannot exceed item gross amount.',
                    ]);
                }

                $lineTotalCents =
                    $grossCents - $itemDiscountCents;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,

                    // Save product information as snapshots.
                    'product_name' => $product->translations()
                        ->where('locale', 'ps')
                        ->value('name') ?? $product->sku,

                    'unit_name' => $product->unit->name,

                    'quantity' => $this->formatMinorUnits(
                        $quantityMilli,
                        3
                    ),

                    'unit_price' => $this->formatMinorUnits(
                        $unitPriceCents,
                        2
                    ),

                    'discount_amount' => $this->formatMinorUnits(
                        $itemDiscountCents,
                        2
                    ),

                    'line_total' => $this->formatMinorUnits(
                        $lineTotalCents,
                        2
                    ),

                    'cost_total' => '0.00',
                ]);

                $subtotalCents += $grossCents;
                $itemDiscountTotalCents += $itemDiscountCents;
            }

            // Invoice-level discount.
            $invoiceDiscountCents = $this->toMinorUnits(
                $data['discount_amount'] ?? 0,
                2
            );

            $availableTotalCents =
                $subtotalCents - $itemDiscountTotalCents;

            if (
                $invoiceDiscountCents < 0 ||
                $invoiceDiscountCents > $availableTotalCents
            ) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Invalid invoice discount.',
                ]);
            }

            $totalDiscountCents =
                $itemDiscountTotalCents + $invoiceDiscountCents;

            $totalAmountCents =
                $subtotalCents - $totalDiscountCents;

            $sale->update([
                'subtotal' => $this->formatMinorUnits(
                    $subtotalCents,
                    2
                ),

                'discount_amount' => $this->formatMinorUnits(
                    $totalDiscountCents,
                    2
                ),

                'total_amount' => $this->formatMinorUnits(
                    $totalAmountCents,
                    2
                ),
            ]);

            return $sale->load('items');

        }, attempts: 3);
    }

    /**
     * Complete a draft sale using FIFO inventory costing.
     */
    public function complete(int $saleId): Sale
    {
        return DB::transaction(function () use ($saleId) {

            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($saleId);

            if ($sale->status !== 'draft') {
                throw ValidationException::withMessages([
                    'sale' => 'Only draft sales can be completed.',
                ]);
            }

            $items = $sale->items()
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'sale' => 'A sale must contain at least one item.',
                ]);
            }

            /*
             * Lock product rows in a consistent order.
             * This helps prevent concurrent sales from consuming
             * the same stock simultaneously.
             */
            $productIds = $items
                ->pluck('product_id')
                ->unique()
                ->sort()
                ->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalCostCents = 0;

            foreach ($items as $item) {

                $quantityNeededMilli = $this->toMinorUnits(
                    $item->quantity,
                    3
                );

                if ($quantityNeededMilli <= 0) {
                    throw ValidationException::withMessages([
                        'sale' => 'Sale quantity must be greater than zero.',
                    ]);
                }

                $product = $products->get($item->product_id);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'sale' => 'A product in this sale is unavailable.',
                    ]);
                }

                /*
                 * Get available FIFO batches.
                 *
                 * Oldest received batch is consumed first.
                 */
                $batches = InventoryBatch::query()
                    ->where('product_id', $item->product_id)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('received_at')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $availableQuantityMilli = 0;

                foreach ($batches as $batch) {
                    $availableQuantityMilli += $this->toMinorUnits(
                        $batch->remaining_quantity,
                        3
                    );
                }

                // Never allow negative inventory.
                if ($availableQuantityMilli < $quantityNeededMilli) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for product: {$item->product_name}.",
                    ]);
                }

                $remainingNeededMilli = $quantityNeededMilli;
                $itemCostTotalCents = 0;

                foreach ($batches as $batch) {

                    if ($remainingNeededMilli <= 0) {
                        break;
                    }

                    $batchRemainingMilli = $this->toMinorUnits(
                        $batch->remaining_quantity,
                        3
                    );

                    $batchRemainingCostCents = $this->toMinorUnits(
                        $batch->remaining_cost,
                        2
                    );

                    if ($batchRemainingMilli <= 0) {
                        continue;
                    }

                    // Determine quantity consumed from this batch.
                    $allocatedQuantityMilli = min(
                        $remainingNeededMilli,
                        $batchRemainingMilli
                    );

                    /*
                     * If the whole remaining batch is consumed,
                     * take all its remaining cost.
                     *
                     * Otherwise allocate cost proportionally.
                     */
                    if ($allocatedQuantityMilli === $batchRemainingMilli) {

                        $allocatedCostCents = $batchRemainingCostCents;

                    } else {

                        $allocatedCostCents = $this->multiplyAndRound(
                            $batchRemainingCostCents,
                            $allocatedQuantityMilli,
                            $batchRemainingMilli
                        );
                    }

                    // Average cost for this allocation.
                    $allocationUnitCostCents = $this->multiplyAndRound(
                        $allocatedCostCents,
                        1000,
                        $allocatedQuantityMilli
                    );

                    // Record the FIFO allocation.
                    SaleItemAllocation::create([
                        'sale_item_id' => $item->id,
                        'inventory_batch_id' => $batch->id,

                        'quantity' => $this->formatMinorUnits(
                            $allocatedQuantityMilli,
                            3
                        ),

                        'unit_cost' => $this->formatMinorUnits(
                            $allocationUnitCostCents,
                            2
                        ),

                        'total_cost' => $this->formatMinorUnits(
                            $allocatedCostCents,
                            2
                        ),
                    ]);

                    // Reduce the batch inventory and its remaining cost.
                    $newRemainingQuantityMilli =
                        $batchRemainingMilli - $allocatedQuantityMilli;

                    $newRemainingCostCents =
                        $batchRemainingCostCents - $allocatedCostCents;

                    $batch->update([
                        'remaining_quantity' => $this->formatMinorUnits(
                            $newRemainingQuantityMilli,
                            3
                        ),

                        'remaining_cost' => $this->formatMinorUnits(
                            $newRemainingCostCents,
                            2
                        ),
                    ]);

                    $itemCostTotalCents += $allocatedCostCents;

                    $remainingNeededMilli -= $allocatedQuantityMilli;
                }

                if ($remainingNeededMilli > 0) {
                    throw ValidationException::withMessages([
                        'stock' => 'Unable to allocate sufficient inventory.',
                    ]);
                }

                // Save the exact cost of goods sold for this sale item.
                $item->update([
                    'cost_total' => $this->formatMinorUnits(
                        $itemCostTotalCents,
                        2
                    ),
                ]);

                $totalCostCents += $itemCostTotalCents;
            }

            // Complete the sale only after all items are allocated.
            $sale->update([
                'status' => 'completed',
            ]);

            return $sale->load([
                'items.allocations',
                'payments',
            ]);

        }, attempts: 3);
    }

    /**
     * Convert a decimal value into integer minor units.
     *
     * Money: scale 2.
     * Quantity: scale 3.
     */
    private function toMinorUnits(mixed $value, int $scale): int
    {
        $value = trim((string) $value);

        if (! preg_match('/^([+-]?)(\d+)(?:\.(\d+))?$/', $value, $matches)) {
            throw ValidationException::withMessages([
                'items' => 'Invalid numeric value.',
            ]);
        }

        $negative = ($matches[1] ?? '') === '-';

        $whole = $matches[2];
        $fraction = $matches[3] ?? '';

        $factor = 10 ** $scale;

        $extraDigit = 0;

        if (strlen($fraction) > $scale) {
            $extraDigit = (int) $fraction[$scale];
        }

        $fraction = substr(
            str_pad($fraction, $scale, '0'),
            0,
            $scale
        );

        $wholeNumber = (int) $whole;

        if ($wholeNumber > intdiv(PHP_INT_MAX, $factor)) {
            throw ValidationException::withMessages([
                'items' => 'Numeric value is too large.',
            ]);
        }

        $result = ($wholeNumber * $factor) + (int) $fraction;

        if ($extraDigit >= 5) {
            $result++;
        }

        return $negative ? -$result : $result;
    }

    /**
     * Format integer minor units as a decimal string.
     */
    private function formatMinorUnits(int $value, int $scale): string
    {
        $factor = 10 ** $scale;

        $negative = $value < 0;
        $absolute = abs($value);

        $whole = intdiv($absolute, $factor);
        $fraction = $absolute % $factor;

        $formatted = $whole . '.' . str_pad(
            (string) $fraction,
            $scale,
            '0',
            STR_PAD_LEFT
        );

        return $negative ? '-' . $formatted : $formatted;
    }

    /**
     * Multiply two integers and divide with half-up rounding.
     */
    private function multiplyAndRound(
        int $first,
        int $second,
        int $divisor
    ): int {
        if ($divisor <= 0) {
            throw ValidationException::withMessages([
                'items' => 'Invalid calculation divisor.',
            ]);
        }

        if (
            $first !== 0 &&
            abs($second) > intdiv(PHP_INT_MAX, abs($first))
        ) {
            throw ValidationException::withMessages([
                'items' => 'Sale calculation exceeds supported limits.',
            ]);
        }

        $product = $first * $second;

        $negative = $product < 0;
        $absolute = abs($product);

        $quotient = intdiv($absolute, $divisor);
        $remainder = $absolute % $divisor;

        if ($remainder * 2 >= $divisor) {
            $quotient++;
        }

        return $negative ? -$quotient : $quotient;
    }
}