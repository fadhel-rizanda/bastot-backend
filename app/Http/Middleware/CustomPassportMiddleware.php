<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class CustomPassportMiddleware extends CheckClientCredentials
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$scope): Response
    {
        try {
            dd('tai');
            return parent::handle($request, $next, $scope);
        }catch (Exception $e){

            return response()->json([
                'error' => 'Authentication Failed',
                'message' => 'Invalid or missing API token',
                'code' => 401,
                'details' => $e->getMessage()
            ], 401, [],JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        }
    }
}

