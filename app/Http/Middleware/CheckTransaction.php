<?php

namespace App\Http\Middleware;

use Closure;

use App\Http\Controllers\ContactController;

class CheckTransaction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (ContactController::$status) {
            return response()->json([
                'message' => 'Can not updating contact.'
            ], 422);
        }
        return $next($request);
    }
}
