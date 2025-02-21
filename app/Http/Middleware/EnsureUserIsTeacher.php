<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTeacher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()->is_teacher) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized, only teachers are allowed',
                'errors' => [
                    'teacher' => ['Unauthorized, only teachers are allowed'],
                ]
            ], 401);
        }

        return $next($request);
    }
}
