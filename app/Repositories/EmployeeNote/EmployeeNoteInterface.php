<?php

namespace App\Repositories\EmployeeNote;

interface EmployeeNoteInterface
{

    /**
     * GET DATE NOTE EMPLOYEE
     * @param int $userID
     * @return array
     */
    public function getDateNoteEmployee($userId);

    /**
     * LIST OF EMPLOYEE DATE NOTE WITH NOTE DATA
     * @param int $userId
     * @return array
     */
    public function listEmployeeNote($userId);

    /**
     * PAGINATE LIST OF EMPLOYEE DATE NOTE WITH NOTE DATA
     * @param int $userId
     * @param int $show
     * @return Collection
     */
    public function listEmployeeNoteData($userId);

    /**
     * LIST OF NOTE DATA EMPLOYEE WITH DATE
     * THIS QUERY FOR MOBILE APPS
     * @param int $userId
     * @return array
     */
    public function listPaginateEmployeeNote($userId, $show);

    /**
     * GET NOTE DATA EMPLOYEE BY SPESIFIC DATE
     * @param int $userId
     * @param string $date
     * @return object
     */
    public function listEmployeeNoteByDate($userId, $date);

    /**
     * GET DATE OF NOTE EMPLOYEE
     * @param int $userId
     * @param string $date
     * @return object
     */
    public function getEmployeeNoteByDate($userId, $date);

    /**
     * GET NOTE DATE EMPLOYEE BY ID
     * @param int $id
     * @return object
     */
    public function getEmployeeNoteDateById($id);

    /**
     * CHECK IF CURRENT NOTE DATE IS EXISTS OR NOTE
     * @param int $userId
     * @param string $date
     * @return bool
     */
    public function isExistsNote($userId, $date);

    /** 
     * CREATE NEW EMPLOYEE NOTE DATE
     * @param array $data
     * @return object
     */
    public function createEmployeeNote(array $data);

    /**
     * CREATE NEW EMPLOYEE NOTE DATA
     * @param array $data
     * @return object
     */
    public function createEmployeeNoteData(array $data);

    /**
     * GET NOTE DATA FROM EMPLOYEE BY ID NOTE DATA
     * @param int $id
     * @return object
     */
    public function getNoteFromDateEmployee($id);

    /**
     * DELETE EMPLOYEE NOTE DATA
     * @param int $id
     * @return bool
     */
    public function deleteEmployeeNote($id);

    /**
     * DELETE NOTE DATE EMPLOYEE
     * @param int $id
     * @return bool
     */
    public function deleteEmployeeNoteDate($id);
}
