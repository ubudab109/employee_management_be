<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToEmployeeOvertimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_overtime', function (Blueprint $table) {
            $table->foreignId('branch_id')->after('employee_id')->constrained('company_branch')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('department_id')->after('branch_id')->constrained('company_division')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_overtime', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('department_id');
        });
    }
}
