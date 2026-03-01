<?php

namespace App\Services\Admin;

use App\Http\Traits\ApiResponses;
use App\Models\User;
use App\Schemas\UserFormSchema;
use Elegant\Sanitizer\Sanitizer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    use ApiResponses;

    public function manage_user_service($request)
    {
        try {
            $androidId = $request->input('id');
            $user = $androidId ? User::find($androidId) : null;

            $schema = new UserFormSchema($user);
            $form = $schema->schema();
            $view = view('canvas.canvas-view', compact('form'))->render();

            return $this->successResponse([
                'message' => 'Form loaded',
                'view' => $view,
            ]);
        } catch (Exception $e) {
            Log::error('manage_user_error', ['message' => $e->getMessage()]);

            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    public function save_user_service($request)
    {
        DB::beginTransaction();
        try {
            $sanitizer = new Sanitizer($request->all(), [
                'android_id' => 'trim|strip_tags|cast:string|empty_string_to_null',
                'is_vip' => 'cast:boolean',
                'video_click_count' => 'trim|cast:integer',
            ]);
            $data = $sanitizer->sanitize();

            $androidId = $data['android_id'] ?? null;
            if (empty($androidId)) {
                return $this->errorResponse([], 'Android ID is required', 422);
            }

            $user = User::find($androidId);
            if ($user) {
                $user->update([
                    'is_vip' => $data['is_vip'] ?? $user->is_vip,
                    'video_click_count' => (int) ($data['video_click_count'] ?? $user->video_click_count),
                ]);
                $message = 'User updated successfully';
            } else {
                User::create([
                    'android_id' => $androidId,
                    'is_vip' => $data['is_vip'] ?? false,
                    'video_click_count' => (int) ($data['video_click_count'] ?? 0),
                ]);
                $message = 'User created successfully';
            }

            DB::commit();

            return $this->successResponse(['message' => $message]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('save_user_error', ['message' => $e->getMessage()]);

            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    public function delete_user_service($request)
    {
        try {
            $androidId = $request->input('id');
            $user = User::findOrFail($androidId);
            $user->delete();

            return $this->successResponse(['message' => 'User deleted successfully']);
        } catch (Exception $e) {
            Log::error('delete_user_error', ['message' => $e->getMessage()]);

            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }
}
