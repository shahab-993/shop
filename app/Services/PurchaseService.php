<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InventoryBatch;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * Create a new purchase with its items.
     */
    public function create(array $data, int $userId): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {

            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw ValidationException::withMessages([
                    'items' => 'A purchase must contain at least one item.',
                ]);
            }

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $userId,
                'invoice_number' => $data['invoice_number'],
                'supplier_invoice_number' =>
                    $data['supplier_invoice_number'] ?? null,
                'purchase_date' => $data['purchase_date'],
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

                $unitCostCents = $this->toMinorUnits(
                    $item['unit_cost'],
                    2
                );

                $itemDiscountCents = $this->toMinorUnits(
                    $item['discount_amount'] ?? 0,
                    2
                );

                if (
                    $quantityMilli <= 0 ||
                    $unitCostCents < 0 ||
                    $itemDiscountCents < 0
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Invalid purchase item values.',
                    ]);
                }

                // Calculate quantity * unit cost in cents.
                $grossCents = $this->multiplyAndRound(
                    $quantityMilli,
                    $unitCostCents,
                    1000
                );

                if ($itemDiscountCents > $grossCents) {
                    throw ValidationException::withMessages([
                        'items' => 'Item discount cannot exceed item gross amount.',
                    ]);
                }

                $lineTotalCents = $grossCents - $itemDiscountCents;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,

                    'product_name' => $product->translations()
                        ->where('locale', 'ps')
                        ->value('name') ?? $product->sku,

                    'unit_name' => $product->unit->name,

                    'quantity' => $this->formatMinorUnits(
                        $quantityMilli,
                        3
                    ),

                    'unit_cost' => $this->formatMinorUnits(
                        $unitCostCents,
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

            $purchase->update([
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

            return $purchase->load('items');

        }, attempts: 3);
    }

    /**
     * Complete a draft purchase and create inventory batches.
     */
    public function complete(int $purchaseId): Purchase
    {
        return DB::transaction(function () use ($purchaseId) {

            $purchase = Purchase::query()
                ->lockForUpdate()
                ->findOrFail($purchaseId);

            if ($purchase->status !== 'draft') {
                throw ValidationException::withMessages([
                    'purchase' => 'Only draft purchases can be completed.',
                ]);
            }

            $items = $purchase->items()
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'purchase' => 'A purchase must contain at least one item.',
                ]);
            }

            // Calculate invoice-level discount.
            $itemDiscountTotalCents = 0;
            $totalAfterItemDiscountCents = 0;

            foreach ($items as $item) {
                $itemDiscountTotalCents += $this->toMinorUnits(
                    $item->discount_amount,
                    2
                );

                $totalAfterItemDiscountCents += $this->toMinorUnits(
                    $item->line_total,
                    2
                );
            }

            $purchaseDiscountCents = $this->toMinorUnits(
                $purchase->discount_amount,
                2
            );

            $invoiceDiscountCents =
                $purchaseDiscountCents - $itemDiscountTotalCents;

            if (
                $invoiceDiscountCents < 0 ||
                $invoiceDiscountCents > $totalAfterItemDiscountCents
            ) {
                throw ValidationException::withMessages([
                    'purchase' => 'Purchase discount calculation is invalid.',
                ]);
            }

            // The purchase date is used as the inventory receipt date.
            $receivedAt = Carbon::parse(
                $purchase->purchase_date->toDateString()
            )->startOfDay();

            $allocatedDiscountCents = 0;
            $lastIndex = $items->count() - 1;

            foreach ($items as $index => $item) {

                $lineTotalCents = $this->toMinorUnits(
                    $item->line_total,
                    2
                );

                $quantityMilli = $this->toMinorUnits(
                    $item->quantity,
                    3
                );

                if ($quantityMilli <= 0) {
                    throw ValidationException::withMessages([
                        'purchase' => 'Purchase quantity must be greater than zero.',
                    ]);
                }

                // Allocate invoice discount proportionally.
                if ($index === $lastIndex) {

                    // Last item receives the remaining discount.
                    $lineInvoiceDiscountCents =
                        $invoiceDiscountCents - $allocatedDiscountCents;

                } elseif ($totalAfterItemDiscountCents > 0) {

                    $lineInvoiceDiscountCents =
                        $this->multiplyAndRound(
                            $invoiceDiscountCents,
                            $lineTotalCents,
                            $totalAfterItemDiscountCents
                        );

                    $allocatedDiscountCents +=
                        $lineInvoiceDiscountCents;

                } else {

                    $lineInvoiceDiscountCents = 0;
                }

                $effectiveLineCostCents =
                    $lineTotalCents - $lineInvoiceDiscountCents;

                if ($effectiveLineCostCents < 0) {
                    throw ValidationException::withMessages([
                        'purchase' => 'Invalid final purchase item cost.',
                    ]);
                }

                // Average unit cost, rounded to 2 decimal places.
                // Exact total cost remains stored separately.
                $effectiveUnitCostCents = $this->multiplyAndRound(
                    $effectiveLineCostCents,
                    1000,
                    $quantityMilli
                );

                $effectiveUnitCost = $this->formatMinorUnits(
                    $effectiveUnitCostCents,
                    2
                );

                $effectiveLineCost = $this->formatMinorUnits(
                    $effectiveLineCostCents,
                    2
                );

                // Create FIFO inventory batch.
                InventoryBatch::create([
                    'product_id' => $item->product_id,

                    'source_type' => 'purchase',
                    'source_id' => $purchase->id,
                    'source_item_id' => $item->id,

                    'received_at' => $receivedAt,

                    'original_quantity' => $this->formatMinorUnits(
                        $quantityMilli,
                        3
                    ),

                    'remaining_quantity' => $this->formatMinorUnits(
                        $quantityMilli,
                        3
                    ),

                    'unit_cost' => $effectiveUnitCost,

                    'original_cost' => $effectiveLineCost,
                    'remaining_cost' => $effectiveLineCost,
                ]);

                // Update the latest purchase price on the product.
                Product::whereKey($item->product_id)
                    ->update([
                        'purchase_price' => $effectiveUnitCost,
                    ]);
            }

            $purchase->update([
                'status' => 'completed',
            ]);

            return $purchase->load('items');

        }, attempts: 3);
    }

    /**
     * Convert a decimal value into integer minor units.
     *
     * Money: scale 2 (cents).
     * Quantity: scale 3 (milli-units).
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

        $fraction = str_pad($fraction, $scale, '0');

        $extraDigit = 0;

        if (strlen($fraction) > $scale) {
            $extraDigit = (int) $fraction[$scale];
            $fraction = substr($fraction, 0, $scale);
        }

        $fraction = substr($fraction, 0, $scale);

        $wholeNumber = (int) $whole;

        if ($wholeNumber > intdiv(PHP_INT_MAX, $factor)) {
            throw ValidationException::withMessages([
                'items' => 'Numeric value is too large.',
            ]);
        }

        $result = ($wholeNumber * $factor) + (int) $fraction;

        if ($extraDigit >= 5) {
            if ($result === PHP_INT_MAX) {
                throw ValidationException::withMessages([
                    'items' => 'Numeric value is too large.',
                ]);
            }

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

        if ($first !== 0 && abs($second) > intdiv(PHP_INT_MAX, abs($first))) {
            throw ValidationException::withMessages([
                'items' => 'Purchase calculation exceeds supported limits.',
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