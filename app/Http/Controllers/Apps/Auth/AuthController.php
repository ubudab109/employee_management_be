<?php

namespace App\Http\Controllers\Apps\Auth;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Models\UserDivisionAssign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Login Process
     * 
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Http\Response
    */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential'        => 'required',
            'password'          => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validation Error', $validator->errors());
        }

        $user = User::where('email', $request->credential)->orWhere('phone_number', $request->credential)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                if (!$user->hasVerifiedEmail()) {
                    return $request->wantsJson()
                        ? $this->sendError('Email Belum Terverifikasi', "Harap memverifikasi email Anda")
                        : $this->sendResponse(array("success" => false), 'Email not verified');
                }

                $token = $user->createToken('auth_token')->plainTextToken;
                return $this->sendResponse([
                    'token'         => $token,
                    'user'          => $user,
                    'assign'        => $user->branch()->with('branch')->first(),
                    'expired_token' => Date::now()->addDay(150),
                ],'Login Successfully');
            }

            return $this->sendUnauthorized('Kredensial Salah', null);
        } else {
            return $this->sendUnauthorized('Email atau Nomor Hp Tidak Ditemukan',null);
        }
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->sendResponse(null, 'Logout Berhasil');
    }
}
