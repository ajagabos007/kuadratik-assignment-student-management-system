<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MergeAttendanceStudentFilter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        $filters = $request->input('filter', []);
        $updated_filters = array_merge($filters, ['student'=> '']);
        $request->merge(['filter' => $updated_filters]);

        return $next($request);
    }
}
