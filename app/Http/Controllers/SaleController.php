<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalePaymentRequest;
use App\Models\Sale;
use App\Services\SalePaymentService;
use Illuminate\Http\RedirectResponse;

class SaleController extends Controller
{
    /**
     * Store a payment for a completed sale.
     */
    public function storePayment(
        StoreSalePaymentRequest $request,
        SalePaymentService $salePaymentService
    ): RedirectResponse {

        $payment = $salePaymentService->create(
            $request->validated(),
            (int) $request->user()->id
        );

        return back()->with(
            'success',
            'Payment recorded successfully.'
        );
    }
}
