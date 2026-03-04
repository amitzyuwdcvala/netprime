<?php

namespace App\Services\Admin;

use App\Http\Traits\ApiResponses;
use App\Models\SubscriptionPlan;
use App\Schemas\PlanFormSchema;
use App\Services\API\SubscriptionService;
use Elegant\Sanitizer\Sanitizer;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlanService
{
    use ApiResponses;

    public function manage_plan_service($request)
    {
        try {
            $id = $request->input('id');
            $plan = $id ? SubscriptionPlan::find($id) : null;
            $schema = new PlanFormSchema($plan);
            $form = $schema->schema();
            $view = view('canvas.canvas-view', compact('form'))->render();
            return $this->successResponse(['message' => 'Form loaded', 'view' => $view]);
        } catch (Exception $e) {
            Log::error('manage_plan_error', ['message' => $e->getMessage()]);
            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    public function save_plan_service($request)
    {
        DB::beginTransaction();
        try {
            $sanitizer = new Sanitizer($request->all(), [
                'id' => 'trim|strip_tags|empty_string_to_null',
                'name' => 'trim|strip_tags|cast:string|empty_string_to_null',
                'amount' => 'trim|cast:float',
                'days' => 'trim|cast:integer',
                'is_popular' => 'cast:boolean',
                'is_active' => 'cast:boolean',
                'sort_order' => 'trim|cast:integer',
            ]);
            $data = $sanitizer->sanitize();
            $featuresRaw = $request->input('features', '');
            $features = array_values(array_filter(array_map('trim', explode("\n", $featuresRaw))));
            $id = $data['id'] ?? null;
            if ($id) {
                $plan = SubscriptionPlan::findOrFail($id);
                $plan->update([
                    'name' => $data['name'] ?? $plan->name,
                    'amount' => $data['amount'] ?? $plan->amount,
                    'days' => (int) ($data['days'] ?? $plan->days),
                    'features' => $features,
                    'is_popular' => $data['is_popular'] ?? $plan->is_popular,
                    'is_active' => $data['is_active'] ?? $plan->is_active,
                    'sort_order' => (int) ($data['sort_order'] ?? $plan->sort_order),
                ]);
                $message = 'Plan updated successfully';
            } else {
                SubscriptionPlan::create([
                    'name' => $data['name'],
                    'amount' => (float) ($data['amount'] ?? 0),
                    'days' => (int) ($data['days'] ?? 0),
                    'features' => $features,
                    'is_popular' => $data['is_popular'] ?? false,
                    'is_active' => $data['is_active'] ?? true,
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                ]);
                $message = 'Plan created successfully';
            }
            DB::commit();
            $this->invalidatePlanRelatedCaches();
            return $this->successResponse(['message' => $message]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('save_plan_error', ['message' => $e->getMessage()]);
            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    public function delete_plan_service($request)
    {
        try {
            $id = $request->input('id');
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->delete();
            $this->invalidatePlanRelatedCaches();
            return $this->successResponse(['message' => 'Plan deleted successfully']);
        } catch (Exception $e) {
            Log::error('delete_plan_error', ['message' => $e->getMessage()]);
            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }


    protected function invalidatePlanRelatedCaches(): void
    {
        Cache::forget(SubscriptionService::CACHE_KEY_PLANS);
        app(DashboardService::class)->clearCache();
    }
}
