<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditColumnToEmployeeSalaryAndEmployeeAttendanceCutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_salary', function (Blueprint $table) {
           $table->boolean('is_temporary')->after('amount')->default(false);
        });

        Schema::table('employee_attendance_cut', function (Blueprint $table){
            $table->string('total')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_salary_and_employee_attendance_cut', function (Blueprint $table) {
            //
        });
    }
}
