<?php

namespace App\Http\Controllers\Apps\MobileApi\Profile;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class ProfileController extends BaseController
{
    //

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'mimes:jpg,jpeg,png|max:2048',
        ], [
            'file.mimes'    => 'Harap upload gambar dengan format JPG, JPEG atau PNG',
            'file.max'      => 'Maksimal size foto adalah 2MB'
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }

        $user = DB::table('users')->find(Auth::user()->id);

        $file = $request->file('file');
        $imageName = storeImages('public/images/profile-pictures/' . $user->id . '/', $file);
        $dataImage['profile_picture'] = URL::to('storage/images/profile-pictures/' . $user->id . '/' . $imageName);
        $user->update($dataImage);

        return $this->sendResponse(array('success' => 1), 'Gambar berhasil diupload');
    }
}
