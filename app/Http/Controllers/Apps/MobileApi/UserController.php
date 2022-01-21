<?php

namespace App\Http\Controllers\Apps\MobileApi;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserDivisionAssign;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    /**
     * GET USER DATA
     */
    public function index()
    {
        $user =  User::find(Auth::user()->id);
        $role = $user->roles()->first();
        if ($role) {
            $userRole = $role->name;
        } else {
            $division = $user->division()->first();
            $userDivisionAssign = UserDivisionAssign::find($division->pivot->id);
            $userRole = $userDivisionAssign->roles()->first()->name;
        }

        return $this->sendResponse([
            'fullname'      => $user->name,
            'nip'           => $user->nip,
            'phone_number'  => $user->phone_number,
            'role'          => $userRole,
            'email'         => $user->email,
        ],'Login Successfully');
    }
}
