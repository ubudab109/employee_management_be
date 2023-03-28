<?php

namespace App\Http\Controllers\Web\ClaimType;

use App\Http\Controllers\BaseController;
use App\Services\ClaimTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClaimTypeController extends BaseController
{
    public $services;

    public function __construct(ClaimTypeService $services)
    {
        $this->middleware('userpermissionmanager:claim-type-list',['only' => 'index']);
        $this->middleware('userpermissionmanager:claim-type-create',['only' => 'store']);
        $this->middleware('userpermissionmanager:claim-type-update',['only' => 'update']);
        $this->middleware('userpermissionmanager:claim-type-detail',['only' => 'detail']);
        $this->middleware('userpermissionmanager:claim-type-delete',['only' => 'delete']);
        $this->services = $services;
    }

    
    /**
     * It returns a list of claim types
     * 
     * @param Request $request - This is the request object that is sent to the API.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $claimType = $this->services->listClaimType($request->all());
        return $this->sendResponse($claimType['data'], $claimType['message']);
    }

    /**
     * It returns detail of claim types
     * @param integer $id - Claim Type Id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $claimType = $this->services->detail($id);
        if (!$claimType['status']) {
            return $this->sendError($claimType['message'], null, 404);
        }
        return $this->sendResponse($claimType['data'], $claimType['message']);
    }

    /**
     * It creates a new claim type
     * @param Request $request - Form body request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'max_claim' => 'required', 
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }

        $input = $request->all();
        $input['branch_id'] = branchSelected('sanctum:manager')->id;
        $this->services->create($input);
        return $this->sendResponse(array('success' => 1), 'Claim Type Created Successfully');
    }

    /**
     * It updated the claim type
     * @param Request $request - Form to update
     * @param integer $id - Claim Type Id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'max_claim' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }
        $this->services->update($request->all(), $id);
        return $this->sendResponse(array('success' => 1), 'Claim Type Updated Successfully');
    }

    /**
     * It delete existing claim type
     * @param integer $id - Claim Type Id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->services->delete($id);
        return $this->sendResponse(array('success' => 1), 'Claim Type Deleted Successfully');
    }
}
