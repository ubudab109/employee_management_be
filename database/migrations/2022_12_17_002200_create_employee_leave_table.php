<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeLeaveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_leave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('desc')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('taken')->default(0);
            $table->enum('status', ['0','1','2'])->default('0')->comment('0 pending, 1 approve, 2 rejected');
            $table->enum('type',['permit','paid_leave']);
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
        Schema::dropIfExists('employee_leave');
    }
}
