<?php

namespace Database\Seeders;

use App\Constants\PaymentGatewayCode;
use App\Models\PaymentGateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Razorpay',
                'code' => PaymentGatewayCode::RAZORPAY,
                'is_active' => true,
                'credentials' => [
                    'key_id' => 'rzp_test_SIfXSUz5iJPFX1',
                    'key_secret' => 'qWMKAyeU0knPJ5hlYL291t0E',
                    'webhook_secret' => 'mywebhooksecret123jsashdjlkjdjdskfjskdf',
                    'env' => 'TEST',
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'PhonePe',
                'code' => PaymentGatewayCode::PHONEPE,
                'is_active' => false,
                'credentials' => [
                    'client_id'        => 'M23LZTGSEEHLX_2602250139',       // from PhonePe dashboard
                    'client_secret'    => 'YmVjYzQ4OWUtNmRlYy00OTYyLWI1YjktYTQ5M2I5ZDI5OWIw',   // from PhonePe dashboard
                    'client_version'   => 1,
                    'webhook_username' => 'netprime_webhook', // set in dashboard
                    'webhook_password' => 'Netprime123', // set in dashboard
                    'env'              => 'UAT',
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'PayU',
                'code' => PaymentGatewayCode::PAYU,
                'is_active' => false,
                'credentials' => [
                    'key'               => 'CWJmX6',
                    'salt'              => 'xOhhqnlkDWrUCABgr7KFdWm3WkC1gQuS',
                    'merchant_secret'   => null, // optional: for v3 verify API; if null, salt is used
                    'surl'              => '',   // success callback URL (e.g. https://yoursite.com/api/v1/webhook/payu/success)
                    'furl'              => '',   // failure callback URL (e.g. https://yoursite.com/api/v1/webhook/payu/failure)
                    'env'               => 'TEST', // TEST or PROD
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'Cashfree',
                'code' => PaymentGatewayCode::CASHFREE,
                'is_active' => false,
                'credentials' => [
                    'app_id' => 'TEST1018097913d678670c2e3218230897908101',
                    'secret_key' => 'cfsk_ma_test_9976567161d6113d779b3231655d7219_2ff8c460',
                    'env' => 'TEST', // or PROD
                ],
                'sort_order' => 4,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(
                ['code' => $gateway['code']],
                $gateway
            );
        }
    }
}
