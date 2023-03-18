<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use PDF;

class TestController extends Controller
{
    public function test()
    {
        $data = DB::table('payroll')
        ->where('month', 3)
        ->where('years', 2023)
        ->where('branch_id', 7)
        ->get()->groupBy('employee_id');
        return count($data->toArray());
        // return $path;
    }
}
