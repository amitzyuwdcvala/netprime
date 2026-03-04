<?php

namespace App\Schemas;

use App\Models\User;
use App\Models\SubscriptionPlan;

class UserFormSchema
{
    protected $user;
    protected $plans;
    protected bool $canEditVip;

    public function __construct($user = null)
    {
        $this->user = $user;
        $this->plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Admin can freely edit VIP / plan fields only when:
        // - user is being created, OR
        // - user was manually added by an admin, OR
        // - user has no active subscription.
        if (!$this->user) {
            $this->canEditVip = true;
        } else {
            $this->canEditVip = $this->user->isManuallyAdded() || !$this->user->hasActiveSubscription();
        }
    }

    public function schema(): array
    {
        return [
            'formName' => 'User',
            'formID' => 'user-form-data',
            'saveRoute' => route('admin.users.save'),
            'dataTableID' => 'user-table',
            'fields' => $this->fields(),
            'validations' => $this->validations(),
        ];
    }

    public function fields(): array
    {
        $fields = [
            'android_id' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Android ID',
                'inputType' => 'text',
                'name' => 'android_id',
                'defaultValue' => $this->user ? $this->user->android_id : '',
                'placeHolder' => 'Android device ID',
                'readonly' => (bool) $this->user,
            ],
            'is_vip' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'VIP',
                'inputType' => 'checkbox',
                'name' => 'is_vip',
                'defaultValue' => $this->user ? $this->user->is_vip : false,
                'checkboxLabel' => 'User is VIP',
                // When editing a user that has a real running subscription, prevent toggling VIP
                'disabled' => $this->user ? !$this->canEditVip : false,
            ],
            'video_click_count' => [
                'responsive' => ['col-sm-12', 'mb-3'],
                'label' => 'Video Click Count',
                'inputType' => 'number',
                'name' => 'video_click_count',
                'defaultValue' => $this->user ? $this->user->video_click_count : 0,
                'placeHolder' => '0',
            ],
        ];

        // VIP details: plan + duration. Shown in UI when is_vip is checked.
        $activeSubscription = $this->user ? $this->user->activeSubscription()->first() : null;

        $fields['vip_plan_id'] = [
            'responsive' => ['col-sm-12', 'mb-3', 'vip-details'],
            'label' => 'VIP Subscription Plan',
            'inputType' => 'select',
            'name' => 'vip_plan_id',
            'defaultValue' => $activeSubscription ? $activeSubscription->plan_id : '',
            'options' => $this->plans->pluck('name', 'id')->toArray(),
            'placeHolder' => 'Select a plan',
            'disabled' => $this->user ? !$this->canEditVip : false,
        ];

        return $fields;
    }

    public function validations(): array
    {
        return [
            'rules' => [
                'android_id' => ['required' => true],
                'video_click_count' => ['required' => true, 'min' => 0],
            ],
            'messages' => [
                'android_id' => ['required' => 'Android ID is required'],
                'video_click_count' => ['required' => 'Video click count is required', 'min' => 'Must be 0 or more'],
            ],
        ];
    }
}
