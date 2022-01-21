<?php

namespace App\Http\Controllers\Apps\MobileApi\HomeMain;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\NoteDataEmployeeResponse;
use App\Repositories\CompanySetting\CompanySettingInterface;
use App\Repositories\EmployeeNote\EmployeeNoteInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Monolog\Handler\IFTTTHandler;

class HomeMainController extends BaseController
{   
    public $companySetting, $noteEmployee;

    public function __construct(CompanySettingInterface $companySetting, EmployeeNoteInterface $noteEmployee)
    {
        $this->companySetting = $companySetting;
        $this->noteEmployee = $noteEmployee;
    }


    /**
     * GET WORKING HOURS OF COMPANY
     * 
     * @return \Illuminate\Http\Response
    */
    public function workingHours()
    {
        $data = array();
        $data['entry_hourse'] = $this->companySetting->getValueCompanySetting('company_entry_hours');
        $data['out_hours'] = $this->companySetting->getValueCompanySetting('company_out_hours');

        return $this->sendResponse($data, 'Data Fetched Successfully');
    }

    /**
     * GET EVENT DATE OF EMPLOYEE
     * 
     * @return \Illuminate\Http\Response
    */
    public function getEventDate()
    {
        $data = array();
        $noteDate = $this->noteEmployee->listEmployeeNoteData(Auth::user()->id);
        foreach ($noteDate as $date) {
            $res['id'] = $date->id;
            $res['date'] = $date->noteDate->date;
            $res['color'] = $date->color;
            $res['time'] = $date->time;
            $res['note'] = $date->note;
            array_push($data, $res);
        }
        // $noteDateData = $this->noteEmployee
        return $this->sendResponse($data, 'Data Fetched Successfully');    
    }

    /**
     * GET NOTE OF EMPLOYEE BY DATE
     * @param \Illuminate\Http\Request1
     * @return \Illuminate\Http\Response
    */
    public function employeeNote(Request $request)
    {
        return $this->sendResponse($this->noteEmployee->listEmployeeNoteByDate(Auth::user()->id, $request->date), 'Data Fetched Successfuly');
    }

    /**
     * CREATE NEW NOTE IN SPESIFIC DAYS AND TIME OF EMPLOYEE
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
    */
    public function createNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date'      => 'required',
            'time'      => 'required',
            'note'      => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendBadRequest('Validation Error',$validator->errors());
        }

        DB::beginTransaction();
        try {
            $input = $request->all();
            $input['user_id'] = Auth::user()->id;
            $input['color']   = $request->color;
            $isNoteDateExists = $this->noteEmployee->isExistsNote($input['user_id'], $input['date']);
            if ($isNoteDateExists) {
                $noteDate = $this->noteEmployee->getEmployeeNoteByDate($input['user_id'], $input['date']);
            } else {
                $noteDate = $this->noteEmployee->createEmployeeNote($input);
            }

            $note = $this->noteEmployee->createEmployeeNoteData([
                'noted_id' => $noteDate->id,
                'color'    => $input['color'],
                'time'     => $input['time'],
                'note'     => $input['note']
            ]);

            $response = new NoteDataEmployeeResponse($note);
            DB::commit();
            return $this->sendResponse($response, 'Note Berhasil Dibuat');
        } catch (\Exception $err) {
            DB::rollBack();
            return $this->sendError('Internal Server Error',$err->getMessage().' '. $err->getLine());
        }
    }

    /**
     * CREATE NEW NOTE IN SPESIFIC DAYS AND TIME OF EMPLOYEE
     * @param \App\Models\UserNotedData $id
     * @return \Illuminate\Http\Response
    */
    public function deleteEmployeeNote($id)
    {
        /* GET NOTE DATA FIRST TO CHECK THE NUMBER OF DATA RECORDS ON A RELATED DATE */
        $noteEmployee = $this->noteEmployee->getNoteFromDateEmployee($id);

        /* DELETE NOTE DATE EMPLOYEE */
        $this->noteEmployee->deleteEmployeeNote($id);

        /* CHECKINF IF CURREN DATE NOT HAVE NOTE DATA AGAIN */
        $noteDate = $this->noteEmployee->getEmployeeNoteDateById($noteEmployee->noted_id);
        $countNote = $noteDate->getTotalNoteAttribute();
        if ($countNote < 1) {
            $dataCount = 0;
            /* IF NOTE DATA IN CURRENT DATE IS 0 THEN NOTE DATE WILL DELETED */
            $this->noteEmployee->deleteEmployeeNoteDate($noteEmployee->noted_id);
        }
        $dataCount = $countNote;

        return $this->sendResponse(array('success' => 1, 'count' => $dataCount), 'Note Berhasil Dihapus');
    }
}
