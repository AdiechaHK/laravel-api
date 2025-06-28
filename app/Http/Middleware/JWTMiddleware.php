<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return JsonResponse|Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse|Response|RedirectResponse
    {
        dd("test in middleware");
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['message' => 'Token is invalid'], 401);
            } elseif ($e instanceof TokenExpiredException) {
                return response()->json(['message' => 'Token has expired'], 401);
            } else {
                return response()->json(['message' => 'Authorization token not found'], 401);
            }
        }

        $response = $next($request);
        if ($response instanceof JsonResponse || $response instanceof Response || $response instanceof RedirectResponse) {
            return $response;
        }
        return response()->json($response);
    }
} 