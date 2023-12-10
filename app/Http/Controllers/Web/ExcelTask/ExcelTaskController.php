<?php

namespace App\Http\Controllers\Web\ExcelTask;

use App\Http\Controllers\BaseController;
use App\Services\ExcelTaskServices;
use Illuminate\Http\Request;

class ExcelTaskController extends BaseController
{
    public $services;

    public function __construct(ExcelTaskServices $services)
    {
        $this->services = $services;
    }

    /**
     * REQUEST LIST EXCEL TASK FROM SERVICES
     * @param Request $request
     * @return Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $this->services->list($request->all());
        return $this->sendResponse($data['data'], $data['message']);
    }
}
