<?php

namespace App\Schemas;

use App\Constants\PaymentGatewayCode;
use App\Models\PaymentGateway;

class GatewayFormSchema
{
    protected ?PaymentGateway $gateway;

    public function __construct(?PaymentGateway $gateway = null)
    {
        $this->gateway = $gateway;
    }

    public function schema(): array
    {
        return [
            'formName' => 'Payment Gateway',
            'formID' => 'gateway-form-data',
            'saveRoute' => route('admin.gateways.save'),
            'dataTableID' => 'payment-gateway-table',
            'fields' => $this->fields(),
            'validations' => $this->validations(),
        ];
    }

    protected function credentialKeysByCode(): array
    {
        return [
            PaymentGatewayCode::RAZORPAY => ['key_id', 'key_secret', 'webhook_secret'],
            PaymentGatewayCode::PHONEPE => ['client_id', 'client_secret', 'webhook_username', 'webhook_password'],
            PaymentGatewayCode::PAYU => ['key', 'salt', 'surl', 'furl'],
            PaymentGatewayCode::CASHFREE => ['app_id', 'secret_key'],
        ];
    }

    public function fields(): array
    {
        $cred = $this->gateway ? ($this->gateway->credentials ?? []) : [];
        $code = $this->gateway ? $this->gateway->code : '';

        $fields = [
            'id' => [
                'inputType' => 'hidden',
                'name' => 'id',
                'defaultValue' => $this->gateway ? $this->gateway->id : '',
            ],
            'name' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Name',
                'inputType' => 'text',
                'name' => 'name',
                'defaultValue' => $this->gateway ? $this->gateway->name : '',
                'placeHolder' => 'e.g. Razorpay',
            ],
            'code' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Code',
                'inputType' => 'text',
                'name' => 'code',
                'defaultValue' => $code,
                'placeHolder' => 'e.g. razorpay',
                'readonly' => (bool) $this->gateway,
            ],
            'display_name' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Display Name',
                'inputType' => 'text',
                'name' => 'display_name',
                'defaultValue' => $this->gateway ? $this->gateway->display_name : '',
                'placeHolder' => 'Optional display name',
            ],
            'is_active' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Active',
                'inputType' => 'checkbox',
                'name' => 'is_active',
                'defaultValue' => $this->gateway ? $this->gateway->is_active : true,
                'checkboxLabel' => 'Gateway is active',
            ],
            'sort_order' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Sort Order',
                'inputType' => 'number',
                'name' => 'sort_order',
                'defaultValue' => $this->gateway ? $this->gateway->sort_order : 0,
                'placeHolder' => '0',
            ],
        ];

        $allKeys = $this->credentialKeysByCode();
        $keys = $code && isset($allKeys[$code]) ? $allKeys[$code] : ($allKeys[PaymentGatewayCode::RAZORPAY] ?? []);

        foreach ($keys as $key) {
            $fields['credential_' . $key] = [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'inputType' => 'text',
                'name' => 'credentials[' . $key . ']',
                'defaultValue' => $cred[$key] ?? '',
                'placeHolder' => '',
            ];
        }

        return $fields;
    }

    public function validations(): array
    {
        $rules = [
            'name' => ['required' => true],
            'code' => ['required' => true],
            'sort_order' => ['required' => false],
        ];
        $messages = [
            'name' => ['required' => 'Name is required'],
            'code' => ['required' => 'Code is required'],
        ];

        return ['rules' => $rules, 'messages' => $messages];
    }
}
