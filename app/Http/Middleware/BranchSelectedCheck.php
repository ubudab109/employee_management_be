<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BranchSelectedCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!isSuperAdmin()) {
            if ($request->header('Branch-Selected') && $request->header('Branch-Selected') != null) {
                return $next($request);
            }
    
            return response()->json([
                'success'   => false,
                'message'   => 'Please add Branch-Selected in Header Request',
            ], 422);
        }

        return $next($request);
    }
}
