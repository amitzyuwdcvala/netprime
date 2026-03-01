<?php

namespace App\Schemas;

use App\Models\User;

class UserFormSchema
{
    protected $user;

    public function __construct($user = null)
    {
        $this->user = $user;
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
        return [
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
