<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            abort(response()->json([
                'message' => 'Unauthenticated',
                'error' => 'Token manquant ou invalide'
            ], 401));
        }
    }
}
