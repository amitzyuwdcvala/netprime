<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Http\Requests\API\CreateOrderRequest;
use App\Http\Requests\API\VerifyPaymentRequest;
use App\Services\API\PaymentService;

class PaymentController extends Controller
{
    use ApiResponses;

    public $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create payment order
     */
    public function create_order(CreateOrderRequest $request)
    {
        return $this->paymentService->create_order_service($request);
    }

    /**
     * Verify payment
     */
    public function verify(VerifyPaymentRequest $request)
    {
        return $this->paymentService->verify_payment_service($request);
    }
}

