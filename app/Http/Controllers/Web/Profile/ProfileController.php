<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\BaseController;
use App\Models\UserManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all() ,[
            'current_password'  => 'required',
            'password'          => 'required|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $currentPassword = Hash::check($request->current_password, Auth::user()->password);
        if (!$currentPassword) {
            return $this->sendBadRequest('Validator Errors', 'Current Password Is Wrong');
        }

        UserManager::where('id', Auth::user()->id)->update([
            'password' => Hash::make($request->password)
        ]);

        return $this->sendResponse(array('success' => 1), 'Password Successfully Changed');
    }

    public function updateImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $userManager = UserManager::find(Auth::user()->id);
        $file = $request->file('profile_picture');
        $imageName = storeImages('public/images/profile-pictures/' . $userManager->id . '/', $file);
        $dataImage['profile_picture'] = URL::to('storage/images/profile-pictures/' . $userManager->id . '/' . $imageName);
        $userManager->update($dataImage);
        return $this->sendResponse(URL::to('storage/images/profile-pictures/' . $userManager->id . '/' . $imageName), 'Profile Picture Successfully Changed');

    }
}
