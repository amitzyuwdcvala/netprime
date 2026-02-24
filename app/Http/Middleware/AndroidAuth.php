<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponses;

class AndroidAuth
{
    use ApiResponses;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $androidId = $request->header('X-Android-Id')
            ?? $request->header('android-id')
            ?? $request->header('android_id')
            ?? $request->input('android_id');

        if (!$androidId) {
            return $this->unauthorizedResponse([], 'Android ID is required. Send it in header (X-Android-Id) or body (android_id).');
        }

        $user = User::where('android_id', $androidId)->first();

        if (!$user) {
            return $this->unauthorizedResponse([], 'User not found. Please register first.');
        }

        Auth::login($user);

        return $next($request);
    }
}
