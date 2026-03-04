<?php

namespace App\Services\Admin;

use App\Http\Traits\ApiResponses;
use App\Models\User;
use App\Schemas\UserFormSchema;
use App\Models\UserSubscription;
use App\Models\SubscriptionPlan;
use App\Constants\SubscriptionStatus;
use Elegant\Sanitizer\Sanitizer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserService
{
    use ApiResponses;

    public function manage_user_service($request)
    {
        try {
            $androidId = $request->input('id');
            $user = $androidId ? User::with('subscriptions')->find($androidId) : null;

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
                'video_click_count' => 'trim|cast:integer',
                'vip_plan_id' => 'trim|strip_tags|empty_string_to_null',
            ]);
            $data = $sanitizer->sanitize();

            // Important: handle checkbox explicitly so "unchecked" becomes false, not "keep old value"
            $isVipRequested = $request->boolean('is_vip');

            $androidId = $data['android_id'] ?? null;
            if (empty($androidId)) {
                return $this->errorResponse([], 'Android ID is required', 422);
            }

            $user = User::find($androidId);

            // Determine if admin is allowed to change VIP / plan for this user.
            $canEditVip = true;
            if ($user) {
                $canEditVip = $user->isManuallyAdded() || !$user->hasActiveSubscription();
            }
            if ($user) {
                $updatePayload = [
                    'video_click_count' => (int) ($data['video_click_count'] ?? $user->video_click_count),
                ];
                if ($canEditVip) {
                    // Always respect the checkbox state when admin is allowed to edit VIP
                    $updatePayload['is_vip'] = $isVipRequested;
                }
                $user->update($updatePayload);
                $message = 'User updated successfully';
            } else {
                $user = User::create([
                    'android_id' => $androidId,
                    'is_vip' => $isVipRequested,
                    'video_click_count' => (int) ($data['video_click_count'] ?? 0),
                    'added_by' => Auth::guard('admin')->id(),
                ]);
                $message = 'User created successfully';
                $canEditVip = true; // freshly created manually
            }

            // Handle manual VIP subscription assignment when allowed.
            if ($canEditVip) {
                // Final VIP state is whatever the admin selected in the form
                $isVip = $isVipRequested;
                $vipPlanId = $data['vip_plan_id'] ?? null;

                if ($isVip) {
                    // VIP must have a valid plan selected
                    $plan = $vipPlanId ? SubscriptionPlan::find($vipPlanId) : null;
                    if (!$plan) {
                        DB::rollBack();
                        return $this->errorResponse([], 'Please select a valid subscription plan for VIP user.', 422);
                    }

                    $startDate = Carbon::now()->startOfDay();
                    $days = (int) $plan->days;
                    if ($days <= 0) {
                        DB::rollBack();
                        return $this->errorResponse([], 'Selected plan must have a positive number of days.', 422);
                    }
                    $endDate = (clone $startDate)->addDays($days - 1);

                    // Create or update manual subscription (paid_amount = 0, no gateway)
                    $subscription = UserSubscription::where('android_id', $user->android_id)
                        ->orderByDesc('created_at')
                        ->first();

                    if ($subscription && $subscription->status === SubscriptionStatus::ACTIVE) {
                        $subscription->update([
                            'plan_id' => $vipPlanId,
                            'paid_amount' => 0,
                            'days' => $days,
                            'start_date' => $startDate->toDateString(),
                            'end_date' => $endDate->toDateString(),
                            'status' => SubscriptionStatus::ACTIVE,
                        ]);
                    } else {
                        UserSubscription::create([
                            'android_id' => $user->android_id,
                            'plan_id' => $vipPlanId,
                            'payment_gateway_id' => null,
                            'gateway_order_id' => null,
                            'gateway_payment_id' => null,
                            'paid_amount' => 0,
                            'days' => $days,
                            'start_date' => $startDate->toDateString(),
                            'end_date' => $endDate->toDateString(),
                            'status' => SubscriptionStatus::ACTIVE,
                        ]);
                    }
                } else {
                    // If admin unchecks VIP for a user they are allowed to manage, expire existing subscription.
                    $subscription = UserSubscription::where('android_id', $user->android_id)
                        ->where('status', SubscriptionStatus::ACTIVE)
                        ->first();

                    if ($subscription) {
                        $subscription->update([
                            'status' => SubscriptionStatus::EXPIRED,
                            'end_date' => Carbon::now()->startOfDay()->toDateString(),
                        ]);
                    }
                }
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
