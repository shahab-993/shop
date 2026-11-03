<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'purchase_id' => [
                'nullable',
                'required_without:opening_balance_id',
                'prohibited_with:opening_balance_id',
                Rule::exists('purchases', 'id')
                    ->where('supplier_id', $this->input('supplier_id')),
            ],

            'opening_balance_id' => [
                'nullable',
                'required_without:purchase_id',
                'prohibited_with:purchase_id',
                Rule::exists('supplier_opening_balances', 'id')
                    ->where('supplier_id', $this->input('supplier_id')),
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
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
}