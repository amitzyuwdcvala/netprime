# NetPrime Dashboard & Movie App API - Coding Rules & Standards

> **This file defines the absolute coding standards for this project.**
> **Every file, class, method, and line of code MUST follow these rules.**
> **Architecture modeled after the Transport project (Laravel 11 service-oriented architecture).**

---

## 1. PROJECT IDENTITY

- **Project Name**: NetPrime Dashboard & Movie App API
- **Framework**: Laravel 11 (PHP 8.2+), **Database**: MySQL
- **Auth**: Sanctum (API tokens for mobile) + Session (admin dashboard)
- **Payment**: Razorpay, PhonePe, PayU, Cashfree (one active at a time)
- **Admin Panel**: Blade + Bootstrap 5 + jQuery + Yajra DataTables
- **API Consumers**: Android/iOS mobile app

---

## 2. DIRECTORY STRUCTURE (MANDATORY)

```
app/
├── Constants/                  # Type-safe constant classes
├── DataTables/                 # YajraDataTable classes
├── Exports/                    # Excel export classes
├── Helpers/                    # Auto-loaded helper functions
├── Http/
│   ├── Controllers/
│   │   ├── API/                # Mobile app API controllers
│   │   ├── Admin/              # Admin dashboard controllers
│   │   └── Authentication/     # Auth controllers
│   ├── Middleware/
│   │   ├── API/                # API-specific middleware
│   │   └── RouteClassifier.php
│   ├── Requests/
│   │   ├── API/                # API request validators
│   │   └── Admin/              # Admin request validators
│   └── Traits/
│       └── ApiResponses.php    # Unified response trait
├── Models/                     # Eloquent models (one per table)
├── Schemas/                    # Form schema definitions
├── Services/
│   ├── API/                    # API-facing services
│   ├── Admin/                  # Admin-facing services
│   └── Payment/                # Payment gateway services
└── View/Components/            # Blade component classes
resources/views/
├── admin/                      # Admin dashboard views
├── canvas/canvas-view.blade.php # Offcanvas form renderer
├── components/                 # Reusable Blade components
│   ├── common/ | form/ | datatable/ | modal/
├── layouts/                    # app, sidebar, header, footer
routes/
├── api.php → api/auth.php, api/routes.php
├── web.php → web/admin.php, web/auth.php
```

---

## 3. CONTROLLER RULES

### 3.1 Thin Controllers — Zero business logic

```php
class SubscriptionController extends Controller
{
    use ApiResponses;
    public $subscriptionServices;

    public function __construct(SubscriptionServices $subscriptionServices)
    {
        $this->subscriptionServices = $subscriptionServices;
    }

    public function get_plans(Request $request)
    {
        return $this->subscriptionServices->get_plans_service($request);
    }
}
```

### 3.2 Admin Controller viewData (MANDATORY)

```php
public function __construct(UserManagementServices $services)
{
    $this->services = $services;
    $this->viewData = [
        'title' => 'Manage Users', 'permission' => 'user',
        'prefix' => 'user_', 'dataTableID' => 'user-table',
        'canvasId' => 'manage-record', 'canvasSize' => 'canvas-sm',
        'canvasHeading' => 'Manage User',
        'deleteRoute' => route('admin.delete.user'),
        'manageRoute' => route('admin.manage.user'),
        'editRoute' => '', 'additionalRoute' => [''],
    ];
}
```

### 3.3 Admin Index uses DataTable class

```php
public function index(UserDataTable $dataTable)
{
    return $dataTable->render('admin.users.index', ['viewData' => $this->viewData]);
}
```

### 3.4 Naming: `snake_case` methods, constructor DI, `ApiResponses` trait always

---

## 4. SERVICE LAYER RULES

### 4.1 ALL business logic in Services. Class: `ResourceServices.php`. Methods: `action_resource_service()`

### 4.2 EVERY service method pattern:

```php
public function create_order_service($request)
{
    DB::beginTransaction();
    try {
        $sanitizer = new Sanitizer($request->all(), [
            'plan_id' => 'trim|strip_tags|cast:integer',
        ]);
        $data = $sanitizer->sanitize();
        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        // business logic...
        DB::commit();
        return $this->okResponse(['message' => 'Order created', 'data' => $order]);
    } catch (Exception $e) {
        DB::rollBack();
        return $this->errorResponse([], __('errors.http.sww'), 500);
    }
}
```

### 4.3 Canvas Form Loading Pattern:

```php
public function manage_plan_service($request)
{
    try {
        $id = $request->id;
        $schema = new SubscriptionPlanFormSchema(!empty($id) ? SubscriptionPlan::find($id) : null);
        $form = $schema->schema();
        $view = view('canvas.canvas-view', compact('form'))->render();
        return $this->successResponse(['status' => 'success', 'view' => $view, 'message' => 'Canvas loaded']);
    } catch (Exception $e) {
        return $this->errorResponse([], __('errors.http.sww'), 500);
    }
}
```

### 4.4 Input Sanitization (MANDATORY on every user input):

```php
$sanitizer = new Sanitizer($request->all(), [
    'name' => 'trim|strip_tags|cast:string|empty_string_to_null',
    'amount' => 'trim|strip_tags|cast:integer',
]);
```

---

## 5. API RESPONSE STANDARD

### 5.1 `ApiResponses` trait MUST be on every controller and service.

### 5.2 Response formats:

**API (mobile)**: `{ "status": true, "message": "...", "data": {...} }` or `{ "status": false, "message": "..." }`
**Web (admin AJAX)**: `{ "status": "success", "message": "...", "view": "<html>" }` or `{ "status": "error", "message": "..." }`

### 5.3 RouteClassifier middleware sets `$request->attributes->set('route', 'api')` on API routes. Web is `'web'`.

### 5.4 HTTP codes via Symfony constants ONLY: `Response::HTTP_OK`, `Response::HTTP_CREATED`, etc.

### 5.5 Available methods: `successResponse`, `okResponse`, `createdResponse`, `errorResponse`, `badRequestResponse`, `unauthorizedResponse`, `forbiddenResponse`, `notFoundResponse`, `unprocessableResponse`, `customError` (web), `customErrorAPI` (api).

---

## 6. FORM SCHEMA SYSTEM

### 6.1 Every admin form defined via Schema class:

```php
class SubscriptionPlanFormSchema
{
    public $plan;
    public function __construct($plan = null) { $this->plan = $plan; }

    public function schema()
    {
        return [
            'formName' => 'Plan Form', 'formID' => 'plan-form',
            'saveRoute' => route('admin.save.plan'),
            'dataTableID' => 'plan-table',
            'fields' => $this->fields(), 'validations' => $this->validations(),
        ];
    }

    public function fields()
    {
        return [
            'plan_id' => ['inputType' => 'hidden', 'name' => 'plan_id',
                'defaultValue' => $this->plan->id ?? ''],
            'name' => ['responsive' => ['col-sm-12', 'mb-3'], 'label' => 'Plan Name',
                'inputType' => 'text', 'name' => 'name',
                'defaultValue' => $this->plan->name ?? '',
                'placeHolder' => 'Enter plan name'],
        ];
    }

    public function validations()
    {
        return [
            'rules' => ['name' => ['required' => true, 'minlength' => 3]],
            'messages' => ['name' => ['required' => 'Please enter plan name']],
        ];
    }
}
```

### 6.2 Rendered via `canvas-view.blade.php` + `form-builder` component.

---

## 7. DATATABLE RULES

```php
class UserDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('#', function ($q) { static $i = 0; return ++$i; })
            ->addColumn('action', '<edit/delete buttons with data-id>')
            ->rawColumns(['#', 'action']);
    }
    public function query(User $model): QueryBuilder { return $model->newQuery(); }
    public function html(): HtmlBuilder
    {
        return $this->builder()->setTableId('user-table')
            ->columns($this->getColumns())->responsive(true)
            ->parameters(['pageLength' => 25]);
    }
}
```

---

## 8. FRONTEND / JAVASCRIPT RULES

### 8.1 Offcanvas Form Flow (jQuery):

1. Click edit/add → AJAX GET `manageRoute` with `id` → service returns rendered HTML
2. Inject HTML into offcanvas body → show offcanvas
3. Initialize jQuery Validate on dynamic form using schema validations
4. Submit via AJAX POST to `saveRoute` → service processes → returns success/error JSON
5. On success: close offcanvas, reload DataTable `$('#tableId').DataTable().ajax.reload()`

### 8.2 Delete Flow:

1. Click delete → confirm modal → AJAX POST `deleteRoute` with `id`
2. On success: reload DataTable

### 8.3 DataTable Init: Yajra handles server-side — never manually init `$.DataTable()` on Yajra tables.

### 8.4 AJAX standard:

```javascript
$.ajax({
    url: route, type: 'POST',
    data: formData, processData: false, contentType: false,
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    success: function(res) {
        if (res.status === 'success') { /* reload table, close canvas, toast */ }
    },
    error: function(xhr) { /* show error toast */ }
});
```

---

## 9. ROUTING RULES

### 9.1 API: versioned `api/v1`, modular files, `route_classifier` middleware

```php
Route::prefix('v1')->middleware(['route_classifier'])->group(function () {
    require __DIR__ . '/api/auth.php';
    require __DIR__ . '/api/routes.php';
});
```

### 9.2 Protected routes: `auth:sanctum` + role middleware

### 9.3 Web admin routes: `auth` + session middleware, grouped under `admin` prefix

---

## 10. CONSTANTS SYSTEM

Type-safe constants as classes. NEVER hardcode status strings/numbers:

```php
class SubscriptionStatus {
    const ACTIVE = 'active';
    const EXPIRED = 'expired';
    const CANCELLED = 'cancelled';
}
// Usage: SubscriptionStatus::ACTIVE
```

---

## 11. MODEL RULES

- One model per table, singular name (`User`, `Order`)
- Define `$fillable`, `$casts`, relationships
- Use `HasFactory`, `SoftDeletes` where needed
- Relationships: `hasOne`, `hasMany`, `belongsTo`, `belongsToMany`

---

## 12. PAYMENT GATEWAY RULES

- Interface: `PaymentGatewayInterface` with `createOrder()`, `verifyPayment()`
- Manager: `PaymentGatewayManager` resolves active gateway from DB
- Each gateway: `RazorpayService`, `PhonePeService`, etc. implements interface
- Webhook: separate `WebhookController`, verify signatures, update payment status
- Credentials stored encrypted in `payment_gateways` table, NOT in `.env`
- Only ONE gateway active at a time (admin toggle)

---

## 13. MIDDLEWARE RULES

- `RouteClassifier` → sets `api`/`web` on every request
- `CheckUserDevice` → validates device token for API
- Role middlewares → `is_admin`, `is_user` for route protection
- Always register in `bootstrap/app.php` with aliases

---

## 14. HELPER FILES

Auto-loaded via `composer.json`. Wrap in `if (!function_exists())`:

```php
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) { return '₹' . number_format($amount, 2); }
}
```

---

## 15. ERROR HANDLING

- All services: `try/catch` with `DB::beginTransaction()` + `rollBack()`
- Never expose exception details to client
- Use `__('errors.http.sww')` for generic error messages
- Log errors: `Log::error('context', ['error' => $e->getMessage()])`
