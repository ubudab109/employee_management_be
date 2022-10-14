<?php

namespace App\Http\Controllers\Web\CompanyDivision;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Services\CompanyDivisionServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyDivisionController extends BaseController
{

    public $services;

    public function __construct(CompanyDivisionServices $services)
    {
        $this->middleware('userpermissionmanager:department-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:department-create',['only' => 'store']);
        $this->middleware('userpermissionmanager:department-update',['only' => 'update']);
        $this->middleware('userpermissionmanager:department-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:department-delete',['only' => 'delete']);
        $this->services = $services;
    }
    /**
     * Display a listing company division.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $getData = $this->services->list($request);
        if ($getData['type'] == 'paginate') {
            $data = new PaginationResource($getData['data']);
        } else {
            $data = $getData['data'];
        }

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * Store a newly created division.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'division_name' => 'required',
            'division_code' => '',
            'style_color'   => ''
        ]);

        if ($validate->fails()) {
            return $this->sendBadRequest('Validator Errors', $validate->errors());
        }
        
        $input = $request->all();

        $create = $this->services->create($input);
        if (!$create['status']) {
            return $this->sendError($create['data']);
        }
        return $this->sendResponse($create['data'], 'Data Created Successfully');
    }

    /**
     * Display detail company division.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->services->detail($id);
        if (!$data['status']) {
            return $this->sendError($data['data'],null, $data['code']);
        }
        return $this->sendResponse($data['data'], 'Data Fetched Successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all(), [
            'division_name' => 'required',
            'division_code' => '',
            'style_color'   => ''
        ]);

        if ($validate->fails()) {
            return $this->sendBadRequest('Validator Errors', $validate->errors());
        }

        $input = $request->all();

        $data = $this->services->update($input, $id);

        if (!$data['status']) {
            return $this->sendError('Internal Server Error');
        }
        
        return $this->sendResponse($data['data'], 'Data Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $isDeleted = $this->services->delete($id);
        if (!$isDeleted) {
            return $this->sendError('Internal Server Error');
        }
        return $this->sendResponse($isDeleted, 'Data Deleted Successfully');
    }
}
