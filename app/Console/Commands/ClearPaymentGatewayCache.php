<?php

namespace App\Console\Commands;

use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Console\Command;

class ClearPaymentGatewayCache extends Command
{
    protected $signature = 'payment:clear-gateway-cache';

    protected $description = 'Clear the cached active payment gateway so the next request uses the current DB value (e.g. after changing active gateway).';

    public function handle(PaymentGatewayManager $gatewayManager): int
    {
        $gatewayManager->clearCache();
        $this->info('Payment gateway cache cleared. Next create-order will use the active gateway from the database.');

        return Command::SUCCESS;
    }
}
