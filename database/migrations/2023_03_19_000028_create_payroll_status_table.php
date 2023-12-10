<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('company_division')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('month');
            $table->integer('years');
            $table->enum('status', [0, 1, 2])->comment('0 generated, 1 partially sended, 3 all sended');
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
        Schema::dropIfExists('payroll_status');
    }
}
