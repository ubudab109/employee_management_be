<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeAttendanceLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_attendance_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_attendance_id')->constrained('employee_attendance', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('latitude');
            $table->string('longitude');
            $table->enum('clock_type',['0','1'])->comment('0: in, 1 : out');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_attendance_location');
    }
}
