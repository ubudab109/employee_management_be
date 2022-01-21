<?php

namespace App\Repositories\EmployeeNote;

interface EmployeeNoteInterface
{
    public function getDateNoteEmployee($userId);
    public function listEmployeeNote($userId);
    public function listEmployeeNoteData($userId);
    public function listPaginateEmployeeNote($userId, $show);
    public function listEmployeeNoteByDate($userId, $date);
    public function getEmployeeNoteByDate($userId, $date);
    public function getEmployeeNoteDateById($id);
    public function isExistsNote($userId, $date);
    public function createEmployeeNote(array $data);
    public function createEmployeeNoteData(array $data);
    public function getNoteFromDateEmployee($id);
    public function deleteEmployeeNote($id);
    public function deleteEmployeeNoteDate($id);
}