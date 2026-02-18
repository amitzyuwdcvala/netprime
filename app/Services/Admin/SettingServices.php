<?php

namespace App\Services\Admin;

use App\Http\Traits\ApiResponses;
use App\Models\Setting;
use App\Schemas\SettingFormSchema;
use Elegant\Sanitizer\Sanitizer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingServices
{
    use ApiResponses;

    /**
     * Load manage setting canvas (create/edit).
     */
    public function manage_setting_service($request)
    {
        try {
            $id = $request->id;
            $setting = !empty($id) ? Setting::find($id) : null;

            $schema = new SettingFormSchema($setting);
            $form = $schema->schema();
            $view = view('canvas.canvas-view', compact('form'))->render();

            return $this->successResponse([
                'message' => 'Canvas loaded successfully',
                'view' => $view,
            ]);
        } catch (Exception $e) {
            Log::error('manage_setting_error', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    /**
     * Save setting (create or update).
     */
    public function save_setting_service($request)
    {
        DB::beginTransaction();
        try {
            $sanitizer = new Sanitizer($request->all(), [
                'title'       => 'trim|strip_tags|cast:string|empty_string_to_null',
                'key'         => 'trim|strip_tags|cast:string|empty_string_to_null',
                'value'       => 'trim|strip_tags|cast:string|empty_string_to_null',
            ]);
            $data = $sanitizer->sanitize();

            $id = $data['id'] ?? null;

            if (!empty($id)) {
                $setting = Setting::findOrFail($id);
                $setting->update($data);
                $message = 'Setting updated successfully';
            } else {
                Setting::create($data);
                $message = 'Setting created successfully';
            }

            DB::commit();
            return $this->successResponse(['message' => $message]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('save_setting_error', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    /**
     * Delete a setting.
     */
    public function delete_setting_service($request)
    {
        try {
            $sanitizer = new Sanitizer($request->all(), [
                'id' => 'trim|strip_tags|cast:integer',
            ]);
            $data = $sanitizer->sanitize();

            $setting = Setting::findOrFail($data['id']);
            $setting->delete();

            return $this->successResponse(['message' => 'Setting deleted successfully']);
        } catch (Exception $e) {
            Log::error('delete_setting_error', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }
}
