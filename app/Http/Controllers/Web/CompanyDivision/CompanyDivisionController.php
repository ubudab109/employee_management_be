<?php

namespace App\Http\Controllers\Web\CompanyDivision;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Repositories\CompanyDivision\CompanyDivisionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyDivisionController extends BaseController
{

    public $companyDivisionRepository;

    public function __construct(CompanyDivisionInterface $companyDivisionRepository)
    {
        $this->middleware('userpermissionmanager:department-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:department-create',['only' => 'store']);
        $this->middleware('userpermissionmanager:department-update',['only' => 'update']);
        $this->middleware('userpermissionmanager:department-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:department-delete',['only' => 'delete']);
        $this->companyDivisionRepository = $companyDivisionRepository;
    }
    /**
     * Display a listing company division.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->show != null && $request->show != 'all') {
            $data = $this->companyDivisionRepository->getPaginateDivision($request->keyword, $request->show);
            $res = new PaginationResource($data);
        } else {
            $res = $this->companyDivisionRepository->getAllDivision($request->keyword);
        }

        return $this->sendResponse($res, 'Data Fetched Successfully');
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

        $this->companyDivisionRepository->storeDivision($input);

        return $this->sendResponse(array('success' => true), 'Data Created Successfully');
    }

    /**
     * Display detail company division.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->sendResponse($this->companyDivisionRepository->detailDivision($id), 'Data Fetched Successfully');
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

        $$this->companyDivisionRepository->updateDivision($input, $id);
        
        return $this->sendResponse(array('success' => true), 'Data Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->companyDivisionRepository->deleteDivision($id);
        return $this->sendResponse(array('success' => true), 'Data Deleted Successfully');
    }
}
