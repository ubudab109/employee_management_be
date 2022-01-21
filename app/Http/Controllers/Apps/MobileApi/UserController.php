<?php

namespace App\Http\Controllers\Apps\MobileApi;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserDivisionAssign;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

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

        if ($user->profile_picture == null) {
            $image = URL::to('profile_default/user_default.png');
        } else {
            $image = $user->profile_picture;
        }

        return $this->sendResponse([
            'fullname'      => $user->name,
            'nip'           => $user->nip,
            'phone_number'  => $user->phone_number,
            'role'          => $userRole,
            'email'         => $user->email,
            'picture'       => $image,
        ], 'Login Successfully');
    }
}
