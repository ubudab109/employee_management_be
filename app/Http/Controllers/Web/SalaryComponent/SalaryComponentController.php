<?php

namespace App\Http\Controllers\Web\SalaryComponent;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Services\SalaryComponentServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalaryComponentController extends BaseController
{
    public $services;

    public function __construct(SalaryComponentServices $services)
    {
        $this->services = $services;
    }

    /**
     * LIST SALARY COMPONENT
     * @param Request $request 
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $res = $this->services->list($request->all());
        return $this->sendResponse($res, 'Data Fetched Successfully');
    }

    /**
     * DETAIL SALARY COMPONENT
     * @param integer $id - ID OF SALARY COMPONENT
     * @return Illuminate\Http\Response
     */
    public function show($id)
    {
        $res = $this->services->detail($id);
        if (!$res['status']) {
            return $this->sendError($res['message'], [], 404);
        }
        return $this->sendResponse($res['data'], $res['message']);
    }

    /**
     * STORE NEW SALARY COMPONENT
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $isCreated = $this->services->create($request->all());

        if (!$isCreated['status']) {
            return $this->sendError($isCreated['message']);
        }

        return $this->sendResponse(array('success' => $isCreated['status']), $isCreated['message']);
    }

    /**
     * UPDATE SALARY COMPONENT
     * @param Request $request
     * @param integer $id - ID OF SALARY COMPONENT
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $isUpdated = $this->services->update($request->all(), $id);

        if (!$isUpdated['status']) {
            return $this->sendError($isUpdated['message']);
        }

        return $this->sendResponse(array('success' => 1), $isUpdated['message']);
    }
}
