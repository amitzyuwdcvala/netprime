<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RouteClassifier
{
    /**
     * Classify the route type (api or web) for ApiResponses trait.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $request->attributes->set('route', 'api');
        } else {
            $request->attributes->set('route', 'web');
        }

        return $next($request);
    }
}
