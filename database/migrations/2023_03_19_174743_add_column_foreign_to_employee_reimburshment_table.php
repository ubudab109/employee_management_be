<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnForeignToEmployeeReimburshmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_reimburshment', function (Blueprint $table) {
            $table->foreignId('claim_type_id')->after('id')->nullable()->constrained('claim_type')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_reimburshment', function (Blueprint $table) {
            //
        });
    }
}
