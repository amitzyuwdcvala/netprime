<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponses;
use App\Http\Requests\API\RegisterRequest;
use App\Services\API\AuthService;

class AuthController extends Controller
{
    use ApiResponses;

    public $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register user (first time app launch)
     */
    public function register(RegisterRequest $request)
    {
        return $this->authService->register_service($request);
    }
}
