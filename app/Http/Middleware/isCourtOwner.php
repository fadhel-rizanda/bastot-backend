<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isCourtOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->role_id != 'COURT_OWNER' && $request->user()->role_id != 'SUPER_ADMIN') {
            return response()->json([
                'message' => 'You are not authorized to access this resource.'
            ], 403);
        }
        return $next($request);
    }
}
