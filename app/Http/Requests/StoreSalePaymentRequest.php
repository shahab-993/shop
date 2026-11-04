<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->is_active;
    }

    public function rules(): array
    {
        return [
            'sale_id' => [
                'required',
                'integer',
                Rule::exists('sales', 'id')
                    ->where('status', 'completed'),
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
            ],

            'paid_at' => [
                'required',
                'date',
            ],

            'note' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'sale_id.required' => 'Please select a sale.',
            'sale_id.exists' => 'The selected sale is not available for payment.',

            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be numeric.',
            'amount.gt' => 'Payment amount must be greater than zero.',
            'amount.decimal' => 'Payment amount can have maximum two decimal places.',

            'paid_at.required' => 'Payment date is required.',
            'paid_at.date' => 'Please enter a valid payment date.',

            'note.max' => 'Note cannot exceed 1000 characters.',
        ];
    }
}
