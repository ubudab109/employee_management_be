<?php

namespace App\Repositories\EmployeeNote;

use App\Models\UserNoted;
use App\Models\UserNotedData;
use App\Repositories\BaseRepository;

class EmployeeNoteRepository extends BaseRepository implements EmployeeNoteInterface
{
    /**
    * @var ModelName
    */
    protected $model, $noteData;

    public function __construct(UserNoted $model, UserNotedData $noteData)
    {
      $this->model = $model;
      $this->noteData = $noteData;
    }

    /**
     * GET DATE NOTE EMPLOYEE
     */
    public function getDateNoteEmployee($userId)
    {
      return $this->model->where('user_id', $userId)->get();  
    }

    /**
     * LIST OF EMPLOYEE DATE NOTE WITH NOTE DATA
     */
    public function listEmployeeNote($userId)
    {
      return $this->model->where('user_id', $userId)->with('noteData')->get();
    }

    /**
     * PAGINATE LIST OF EMPLOYEE DATE NOTE WITH NOTE DATA
     */
    public function listPaginateEmployeeNote($userId, $show)
    {
      return $this->model->where('user_id', $userId)->with('noteData')->paginate($show);
    }

    /**
     * LIST OF NOTE DATA EMPLOYEE WITH DATE
     * THIS QUERY FOR MOBILE APPS
     */
    public function listEmployeeNoteData($userId)
    {
      return $this->noteData->whereHas('noteDate', function($q) use ($userId) {
        $q->where('user_id', $userId);
      })->get();
    }

    /**
     * GET NOTE DATA EMPLOYEE BY SPESIFIC DATE
     */
    public function listEmployeeNoteByDate($userId, $date)
    {
      return $this->model->where([
        'user_id' => $userId,
        'date'    => $date,
      ])->with('noteData')->first();
    }

    /**
     * GET DATE OF NOTE EMPLOYEE
     */
    public function getEmployeeNoteByDate($userId, $date)
    {
      return $this->model->where([
        'user_id' => $userId,
        'date'    => $date,
      ])->first();
    }

    /**
     * GET NOTE DATE EMPLOYEE BY ID
    */
    public function getEmployeeNoteDateById($id)
    {
      return $this->model->findOrFail($id);
    }
    
    /**
     * CHECK IF CURRENT NOTE DATE IS EXISTS OR NOTE
     */
    public function isExistsNote($userId, $date)
    {
      return $this->model->where([
        'user_id' => $userId,
        'date'    => $date,
      ])->exists();
    }

    /** 
     * CREATE NEW EMPLOYEE NOTE DATE
     */
    public function createEmployeeNote(array $data)
    {
      return $this->model->create($data);
    }

    /**
     * CREATE NEW EMPLOYEE NOTE DATA
     */
    public function createEmployeeNoteData(array $data) 
    {
      return $this->noteData->create($data);
    }

    /**
     * GET NOTE DATA FROM EMPLOYEE BY ID NOTE DATA
     */
    public function getNoteFromDateEmployee($id)
    {
      return $this->noteData->findOrFail($id);
    }

    /**
     * DELETE EMPLOYEE NOTE DATA
     */
    public function deleteEmployeeNote($id)
    {
      return $this->noteData->findOrFail($id)->delete();
    }

    /**
     * DELETE NOTE DATE EMPLOYEE
    */
    public function deleteEmployeeNoteDate($id)
    {
      return $this->model->findOrFail($id)->delete();
    }
}