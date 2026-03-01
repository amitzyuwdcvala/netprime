<?php

namespace App\Schemas;

use App\Models\SubscriptionPlan;

class PlanFormSchema
{
    protected $plan;

    public function __construct($plan = null)
    {
        $this->plan = $plan;
    }

    public function schema(): array
    {
        return [
            'formName' => 'Subscription Plan',
            'formID' => 'plan-form-data',
            'saveRoute' => route('admin.plans.save'),
            'dataTableID' => 'subscription-plan-table',
            'fields' => $this->fields(),
            'validations' => $this->validations(),
        ];
    }

    public function fields(): array
    {
        $features = $this->plan && is_array($this->plan->features)
            ? implode("\n", $this->plan->features)
            : '';

        return [
            'id' => [
                'inputType' => 'hidden',
                'name' => 'id',
                'defaultValue' => $this->plan ? $this->plan->id : '',
            ],
            'name' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Plan Name',
                'inputType' => 'text',
                'name' => 'name',
                'defaultValue' => $this->plan ? $this->plan->name : '',
                'placeHolder' => 'e.g. Monthly Premium',
            ],
            'amount' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Amount (₹)',
                'inputType' => 'number',
                'name' => 'amount',
                'defaultValue' => $this->plan ? $this->plan->amount : '',
                'placeHolder' => '0.00',
            ],
            'days' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Days',
                'inputType' => 'number',
                'name' => 'days',
                'defaultValue' => $this->plan ? $this->plan->days : '',
                'placeHolder' => '30',
            ],
            'features' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Features (one per line)',
                'inputType' => 'textarea',
                'name' => 'features',
                'defaultValue' => $features,
                'placeHolder' => 'Feature 1' . "\n" . 'Feature 2',
                'rows' => 4,
            ],
            'is_popular' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Popular',
                'inputType' => 'checkbox',
                'name' => 'is_popular',
                'defaultValue' => $this->plan ? $this->plan->is_popular : false,
                'checkboxLabel' => 'Mark as popular plan',
            ],
            'is_active' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Active',
                'inputType' => 'checkbox',
                'name' => 'is_active',
                'defaultValue' => $this->plan ? $this->plan->is_active : true,
                'checkboxLabel' => 'Plan is active',
            ],
            'sort_order' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Sort Order',
                'inputType' => 'number',
                'name' => 'sort_order',
                'defaultValue' => $this->plan ? $this->plan->sort_order : 0,
                'placeHolder' => '0',
            ],
        ];
    }

    public function validations(): array
    {
        return [
            'rules' => [
                'name' => ['required' => true],
                'amount' => ['required' => true],
                'days' => ['required' => true],
            ],
            'messages' => [
                'name' => ['required' => 'Plan name is required'],
                'amount' => ['required' => 'Amount is required'],
                'days' => ['required' => 'Days is required'],
            ],
        ];
    }
}
