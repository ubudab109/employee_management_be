<?php

namespace App\Http\Middleware;

use App\Models\UserDivisionAssign;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;

class UserPermissionCheck
{
    /**
     * Handle to check permission user in current division.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  \App\Models\Permission $permission
     * @param  Auth::guard('api')
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permission, $guard_name = 'auth:employee')
    {
        if (app('auth')->guard($guard_name)->guest()) throw UnauthorizedException::notLoggedIn();
        $permissions = is_array($permission) ? $permission : explode('|', $permission);
        $toManage = Auth::user()->division()->find(Request::header("Division-Selected"));
        $roles = UserDivisionAssign::find($toManage->pivot->id);
        foreach ($permissions as $perm) {
            if ($roles->hasPermissionTo($perm)) {
                return $next($request);
            }
        }

        return response()->json([
            'status' => 403,
            'message' => 'You Dont Have Permission To Access This Resource',
        ], 403);
    }
}
