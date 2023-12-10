<?php

namespace App\Http\Middleware;

use App\Models\UserManagerAssign;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class UserManagerPermissionCheck
{
    /**
     * Handle to check permission user manager role
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  \App\Models\Permission $permission
     * @param  Auth::guard('api')
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission, $guard_name = 'sanctum:manager')
    {
        if (auth($guard_name)->guest()) throw UnauthorizedException::notLoggedIn();
        $permissions = is_array($permission) ? $permission : explode('|', $permission);
        if (Auth::guard($guard_name)->user()->branch()->first() == null) {
            $toManage = Auth::user()->roles[0];
        } else {
            $assign = branchSelected($guard_name)->pivot->id;
            $toManage = UserManagerAssign::find($assign)->roles[0];
        }
        foreach ($permissions as $perm) {
            if ($toManage->hasPermissionTo($perm)) {
                return $next($request);
            }
        }

        return response()->json([
            'status' => 403,
            'message' => 'You Dont Have Permission To Access This Resource',
        ], 403);
    }
}
