<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('company_branch')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('department_id')
            ->nullable()
            ->constrained('company_division')->nullOnDelete()->cascadeOnUpdate();
            $table->string('payroll_code')->nullable();
            $table->string('salary_name');
            $table->double('amount')->default(0);
            $table->enum('type', ['income', 'cut']);
            $table->integer('month');
            $table->integer('years');
            $table->date('generate_date');
            $table->enum('status', ['generated', 'sended']);
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
        Schema::dropIfExists('payroll');
    }
}
