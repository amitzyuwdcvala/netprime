<?php

namespace App\Services\Admin;

use App\Http\Traits\ApiResponses;
use App\Models\PaymentGateway;
use App\Schemas\GatewayFormSchema;
use App\Services\Payment\PaymentGatewayManager;
use Elegant\Sanitizer\Sanitizer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GatewayService
{
    use ApiResponses;

    public function manage_gateway_service($request)
    {
        try {
            $id = $request->input('id');
            $gateway = $id ? PaymentGateway::find($id) : null;

            $schema = new GatewayFormSchema($gateway);
            $form = $schema->schema();
            $view = view('canvas.canvas-view', compact('form'))->render();

            return $this->successResponse([
                'message' => 'Form loaded',
                'view' => $view,
            ]);
        } catch (Exception $e) {
            Log::error('manage_gateway_error', ['message' => $e->getMessage()]);

            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    public function save_gateway_service($request)
    {
        DB::beginTransaction();
        try {
            $sanitizer = new Sanitizer($request->all(), [
                'id' => 'trim|strip_tags|empty_string_to_null',
                'name' => 'trim|strip_tags|cast:string|empty_string_to_null',
                'code' => 'trim|strip_tags|lowercase|cast:string|empty_string_to_null',
                'display_name' => 'trim|strip_tags|cast:string|empty_string_to_null',
                'is_active' => 'cast:boolean',
                'sort_order' => 'trim|cast:integer',
            ]);
            $data = $sanitizer->sanitize();

            $credentials = $request->input('credentials', []);
            if (!is_array($credentials)) {
                $credentials = [];
            }

            $id = $data['id'] ?? null;

            if ($id) {
                $gateway = PaymentGateway::findOrFail($id);
                $gateway->update([
                    'name' => $data['name'] ?? $gateway->name,
                    'display_name' => $data['display_name'] ?? $gateway->display_name,
                    'is_active' => $data['is_active'] ?? $gateway->is_active,
                    'sort_order' => (int) ($data['sort_order'] ?? $gateway->sort_order),
                    'credentials' => array_merge($gateway->credentials ?? [], $credentials),
                ]);
                $message = 'Gateway updated successfully';
            } else {
                PaymentGateway::create([
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'display_name' => $data['display_name'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'credentials' => $credentials,
                ]);
                $message = 'Gateway created successfully';
            }

            DB::commit();
            $this->invalidateGatewayRelatedCaches();

            return $this->successResponse(['message' => $message]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('save_gateway_error', ['message' => $e->getMessage()]);

            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    public function delete_gateway_service($request)
    {
        try {
            $id = $request->input('id');
            $gateway = PaymentGateway::findOrFail($id);
            $gateway->delete();
            $this->invalidateGatewayRelatedCaches();

            return $this->successResponse(['message' => 'Gateway deleted successfully']);
        } catch (Exception $e) {
            Log::error('delete_gateway_error', ['message' => $e->getMessage()]);

            return $this->errorResponse([], __('Something went wrong'), 500);
        }
    }

    /**
     * Invalidate caches that depend on payment gateways (active gateway, dashboard stats).
     */
    protected function invalidateGatewayRelatedCaches(): void
    {
        app(PaymentGatewayManager::class)->clearCache();
        app(DashboardService::class)->clearCache();
    }
}
