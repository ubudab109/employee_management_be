<?php

namespace App\Http\Controllers\Web\CompanySchedule;

use App\Http\Controllers\BaseController;
use App\Services\CompanyScheduleServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CompanyScheduleController extends BaseController
{
    public $services;

    public function __construct(CompanyScheduleServices $services)
    {
        if (config('app.env') != 'development') { 
            $this->middleware('userpermissionmanager:schedule-list',['only' => 'index']);
            $this->middleware('userpermissionmanager:schedule-create',['only' => 'store']);
            $this->middleware('userpermissionmanager:schedule-update',['only' => 'update|updateDefaultSchedule|show']);
            $this->middleware('userpermissionmanager:schedule-delete',['only' => 'delete']);
        }
        $this->services = $services;
    }

    /**
     * LIST DEFAULT SCHEDULE AND LIST HISTORY SCHEDULE
     * 
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $data = $this->services->list();
        return $this->sendResponse($data['data'], $data['message']);
    }
    
    /**
     * DETAIL SCHEDULE
     * @param integer $id - ID Of Schedule
     * @return Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = $this->services->detail($id);

        if (!$data['status']) {
            return $this->sendError($data['message'], null, 404);
        }

        return $this->sendResponse($data['data'], $data['message']);
    }

    /**
     * STORE NEWLY SCHEDULE
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'clock_in'  => 'required',
            'clock_out' => 'required',
            'lat'       => '',
            'long'      => '',
            'map_url'   => '',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }
        /**
         * GET DATA SCHEDULE LIST
         * IF DATA IS NULL OR CURRENT COMPANY BRANCH NOT HAVE ANY SCHEDULE
         * THEN THE FIRST SCHEDULE WILL BE A DEFAULT SCHEDULE
         */
        $input = $request->all();
        $dataSchedule = $this->services->list();
        if (count($dataSchedule['data']['history_schedule']) < 1) {
            $input['is_default'] = true;
        } else {
            $input['is_default'] = false;
        }
        $input['branch_id'] = branchSelected('sanctum:manager')->id;
        $input['code'] = rand(1, 48392300916);
        $created = $this->services->createSchedule($input);
        if (!$created['status']) {
            return $this->sendError(array('success' => 0), $created['message']);
        }
        return $this->sendResponse(array('success' => 1), $created['message']);
    }

    /**
     * UPDATE EXISTING SCHEDULE
     * @param Request $request
     * @param integer $id - ID OF Schedule
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'clock_in'  => 'required',
            'clock_out' => 'required',
            'lat'       => '',
            'long'      => '',
            'map_url'   => '',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Errors', $validator->errors());
        }
        $input = $request->all();
        $updated = $this->services->updateSchedule($input, $id);
        if (!$updated['status']) {
            return $this->sendError(array('success' => 0), $updated['message']);
        }
        return $this->sendResponse(array('success' => 1), $updated['message']);
    }

    /**
     * DELETE SCHEDULE
     * @param integer $id - ID OF Schedule
     * @return Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('company_schedule')->delete($id);
        return $this->sendResponse(array('success' => 1), 'Data deleted successfully');
    }

    /**
     * UPDATE DEFAULT SCHEDULE
     * @param Request $request
     * @param Illuminate\Http\Response
     */
    public function updateDefaultSchedule(Request $request)
    {
        $validator = Validator::make($request->only('schedule_id'), [
            'schedule_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validator Error', $validator->errors());
        }

        $changed = $this->services->changeDefaultSchedule($request->input('schedule_id'));
        if (!$changed['status']) {
            return $this->sendError(array('success' => 0), $changed['message']);
        }
        return $this->sendResponse(array('success' => 1), $changed['message']);
    }
}
