<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\CashTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalePaymentService
{
    /**
     * Register a payment for a completed sale.
     */
    public function create(array $data, int $userId): SalePayment
    {
        return DB::transaction(function () use ($data, $userId) {

            // Lock the sale to prevent concurrent payments.
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($data['sale_id']);

            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages([
                    'sale_id' => 'Payment is allowed only for completed sales.',
                ]);
            }

            $amount = $this->toMinorUnits($data['amount']);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero.',
                ]);
            }

            // Calculate the amount already paid.
            $paidAmount = SalePayment::query()
                ->where('sale_id', $sale->id)
                ->where('status', 'completed')
                ->sum('amount');

            $paidCents = $this->toMinorUnits($paidAmount);

            $totalCents = $this->toMinorUnits($sale->total_amount);

            $remainingCents = $totalCents - $paidCents;

            // Prevent overpayment.
            if ($amount > $remainingCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment cannot exceed the remaining balance.',
                ]);
            }

            // Create the sale payment.
            $payment = SalePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'amount' => $this->formatMoney($amount),
                'paid_at' => $data['paid_at'],
                'note' => $data['note'] ?? null,
                'status' => 'completed',
            ]);

            // Record the cash received.
            CashTransaction::create([
                'user_id' => $userId,
                'type' => 'sale_payment',
                'amount' => $this->formatMoney($amount),
                'transaction_date' => $data['paid_at'],
                'source_type' => 'sale_payment',
                'source_id' => $payment->id,
                'description' => 'Payment received for sale invoice: '
                    . $sale->invoice_number,
                'status' => 'completed',
            ]);

            return $payment->load(['sale', 'user']);

        }, attempts: 3);
    }

    /**
     * Convert money to integer cents.
     */
    private function toMinorUnits(mixed $value): int
    {
        $value = trim((string) $value);

        if (! preg_match('/^([+-]?)(\d+)(?:\.(\d+))?$/', $value, $matches)) {
            throw ValidationException::withMessages([
                'amount' => 'Invalid payment amount.',
            ]);
        }

        $negative = ($matches[1] ?? '') === '-';

        $whole = (int) $matches[2];
        $fraction = $matches[3] ?? '';

        if ($whole > intdiv(PHP_INT_MAX, 100)) {
            throw ValidationException::withMessages([
                'amount' => 'Payment amount is too large.',
            ]);
        }

        $fraction = str_pad($fraction, 3, '0');

        $cents = ($whole * 100) + (int) substr($fraction, 0, 2);

        // Round half-up.
        if ((int) $fraction[2] >= 5) {
            $cents++;
        }

        return $negative ? -$cents : $cents;
    }

    /**
     * Format cents as decimal money.
     */
    private function formatMoney(int $cents): string
    {
        $negative = $cents < 0;
        $absolute = abs($cents);

        $formatted = intdiv($absolute, 100)
            . '.'
            . str_pad((string) ($absolute % 100), 2, '0', STR_PAD_LEFT);

        return $negative ? '-' . $formatted : $formatted;
    }
}
