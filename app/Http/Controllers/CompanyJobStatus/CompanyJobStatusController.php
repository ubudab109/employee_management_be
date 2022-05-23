<?php

namespace App\Http\Controllers\CompanyJobStatus;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PaginationResource;
use App\Repositories\CompanyJobStatus\CompanyJobStatusInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompanyJobStatusController extends BaseController
{
    public $companyJobStatus;

    public function __construct(CompanyJobStatusInterface $companyJobStatus)
    {
        $this->middleware('userpermissionmanager:job-status-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:job-status-detail',['only' => 'show']);
        $this->middleware('userpermissionmanager:job-status-create',['only' => 'store']);
        $this->middleware('userpermissionmanager:job-status-update',['only' => 'update']);
        $this->middleware('userpermissionmanager:job-status-delete',['only' => 'destroy']);
        $this->companyJobStatus = $companyJobStatus;
    }

    /**
     * Display a listing company job status.
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->show != null && $request->show != 'all') {
            $data = $this->companyJobStatus->getPaginateJobStatus($request->keyword, $request->show);
            $res = new PaginationResource($data);
        } else {
            $res = $this->companyJobStatus->getAllJobStatus($request->keyword);
        }

        return $this->sendResponse($res, 'Data Fetched Successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all() ,[
            'name'          => 'required',
            'style_color'   => ''
        ]);

        if ($validate->fails()) return $this->sendBadRequest('Validator Errors', $validate->errors());

        $input = $request->all();
        $this->companyJobStatus->createJobStatus($input);
        return $this->sendResponse(array('success' => true), 'Data Created Successfully');
    }

    /**
     * Display job status by id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->sendResponse($this->companyJobStatus->detailJobStatus($id), 'Data Fetched Successfully');
    }

    /**
     * Update job status data by id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = Validator::make($request->all() ,[
            'name'          => 'required',
            'style_color'   => ''
        ]);

        if ($validate->fails()) return $this->sendBadRequest('Validator Errors', $validate->errors());
        
        $input = $request->all();

        $this->companyJobStatus->updateJobStatus($input, $id);
        
        return $this->sendResponse(array('success' => true), 'Data Updated Successfully');
    }

    /**
     * Remove the specified job statys by id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->companyJobStatus->deleteJobStatus($id);
        return $this->sendResponse(array('success' => true), 'Data Deleted Successfully');
    }
}
