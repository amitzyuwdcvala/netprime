<?php

namespace App\Services\API;

use App\Http\Traits\ApiResponses;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthService
{
    use ApiResponses;

    /**
     * Register user (first time app launch)
     */
    public function register_service($request)
    {
        try {
            DB::beginTransaction();
            // dd($request->all());
            $androidId = $request->input('android_id');

            $user = User::find($androidId);

            if ($user) {
                DB::commit();

                return $this->successResponse([
                    'message' => 'User already registered',
                    'data' => [
                        'user' => [
                            'android_id' => $user->android_id,
                            'is_vip' => $user->is_vip,
                            'video_click_count' => $user->video_click_count,
                        ],
                    ],
                ]);
            }

            // Create new user
            $user = User::create([
                'android_id' => $androidId,
                'is_vip' => false,
                'video_click_count' => 5,
            ]);


            DB::commit();

            return $this->successResponse([
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'android_id' => $user->android_id,
                        'is_vip' => $user->is_vip,
                        'video_click_count' => $user->video_click_count,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AuthService register_service error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse([], 'Registration failed. Please try again.', 500);
        }
    }
}
