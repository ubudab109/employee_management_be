<?php

namespace App\Http\Controllers\Web\CompanySetting;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Services\CompanySettingServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanySettingController extends BaseController
{   
    private $services;

    public function __construct(CompanySettingServices $services)
    {
        $this->services = $services;
    }


    /**
     * List Company Setting
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->services->list();
        return $this->sendResponse($data['data'], $data['message']);
    }

    /**
     * Update data company setting
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data'         => 'required|array',
            'data.*.key'   => 'required',
            'data.*.value' => '',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Failed', $validator->errors());
        }

        $data = $this->services->update($request->only('data'));
        if (!$data['status']) {
            return $this->sendError('Failed', $data['message']);
        }
        return $this->sendResponse(array('success' => 1), $data['message']);
    }
}
