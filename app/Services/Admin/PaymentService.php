<?php

namespace App\Services\Admin;

use App\Models\PaymentTransaction;

class PaymentService
{
    public function getTransactionDetail($id): ?PaymentTransaction
    {
        return PaymentTransaction::with(['plan', 'gateway'])->find($id);
    }
}
