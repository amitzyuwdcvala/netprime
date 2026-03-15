<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;

class IsActiveHelper
{
    public static function toggleIsActive(string $modelClass, $id, string $field = 'is_active'): bool
    {
        try {
            $model = $modelClass::findOrFail($id);
            $model->update([$field => !$model->$field]);

            return (bool) $model->$field;
        } catch (\Exception $e) {
            return false;
        }
    }
}
