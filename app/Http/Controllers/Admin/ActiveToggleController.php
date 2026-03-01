<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\IsActiveHelper;
use App\Models\PaymentGateway;
use App\Models\SubscriptionPlan;
use App\Services\Admin\DashboardService;
use App\Services\API\SubscriptionService;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ActiveToggleController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $modelClass = $request->input('model');
        $id = $request->input('id');
        $field = $request->input('field', 'is_active');

        if (!class_exists($modelClass) || !is_subclass_of($modelClass, 'Illuminate\Database\Eloquent\Model')) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Invalid model',
            ], 422);
        }

        if ($modelClass === PaymentGateway::class && $field === 'is_active') {
            $model = PaymentGateway::find($id);
            if ($model && !$model->is_active) {
                PaymentGateway::where('id', '!=', $id)->update(['is_active' => false]);
            }
        }

        $newStatus = IsActiveHelper::toggleIsActive($modelClass, $id, $field);

        // Invalidate caches when plan or gateway active status changes
        $this->invalidateCachesForToggledModel($modelClass);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Status updated successfully',
        ], 200);
    }

    /**
     * Clear caches that depend on the toggled model (e.g. active gateway, plans list, dashboard).
     */
    protected function invalidateCachesForToggledModel(string $modelClass): void
    {
        if ($modelClass === PaymentGateway::class) {
            app(PaymentGatewayManager::class)->clearCache();
        }
        if ($modelClass === SubscriptionPlan::class) {
            Cache::forget(SubscriptionService::CACHE_KEY_PLANS);
        }
        app(DashboardService::class)->clearCache();
    }
}
