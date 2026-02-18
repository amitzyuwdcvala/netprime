<?php

namespace App\Schemas;

class SettingFormSchema
{
    public $setting;

    public function __construct($setting = null)
    {
        $this->setting = $setting;
    }

    /**
     * Return complete form schema.
     */
    public function schema(): array
    {
        return [
            'formName'    => 'Setting Form',
            'formID'      => 'setting-form-data',
            'saveRoute'   => route('admin.save.setting'),
            'dataTableID' => 'setting-table',
            'fields'      => $this->fields(),
            'validations' => $this->validations(),
        ];
    }

    /**
     * Form fields definition.
     */
    public function fields(): array
    {
        return [
            'id' => [
                'inputType'    => 'hidden',
                'name'         => 'id',
                'defaultValue' => $this->setting->id ?? '',
            ],
            'title' => [
                'responsive'   => ['col-sm-12', 'mb-3'],
                'label'        => 'Title',
                'inputType'    => 'text',
                'name'         => 'title',
                'defaultValue' => $this->setting->title ?? '',
                'placeHolder'  => 'Enter setting title',
            ],
            'key' => [
                'responsive'   => ['col-sm-12', 'mb-3'],
                'label'        => 'Key',
                'inputType'    => 'text',
                'name'         => 'key',
                'defaultValue' => $this->setting->key ?? '',
                'placeHolder'  => 'Enter setting key',
            ],
            'value' => [
                'responsive'   => ['col-sm-12', 'mb-3'],
                'label'        => 'Value',
                'inputType'    => 'textarea',
                'name'         => 'value',
                'defaultValue' => $this->setting->value ?? '',
                'placeHolder'  => 'Enter setting value',
            ],
        ];
    }

    /**
     * jQuery Validate rules and messages.
     */
    public function validations(): array
    {
        return [
            'rules' => [
                'title' => [
                    'required'  => true,
                    'minlength' => 2,
                    'maxlength' => 100,
                ],
                'key' => [
                    'required'  => true,
                    'minlength' => 2,
                    'maxlength' => 100,
                ],
            ],
            'messages' => [
                'title' => [
                    'required'  => 'Please enter a title',
                    'minlength' => 'Title must be at least 2 characters',
                    'maxlength' => 'Title cannot exceed 100 characters',
                ],
                'key' => [
                    'required'  => 'Please enter a key',
                    'minlength' => 'Key must be at least 2 characters',
                    'maxlength' => 'Key cannot exceed 100 characters',
                ],
            ],
        ];
    }
}
