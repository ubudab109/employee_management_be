<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeReimburshmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_reimburshment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('date')->nullable();
            $table->string('claim_type')->nullable();
            $table->double('amount')->default(0);
            $table->enum('status', ['0','1', '2'])->default('0')->comment('0 pending, 1 incomplete, 2 complete');
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
        Schema::dropIfExists('employee_reimburshment');
    }
}
