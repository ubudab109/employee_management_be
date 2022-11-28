<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditDivisionIdToUserDivisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_division_assign', function (Blueprint $table) {
            $table->dropForeign('user_division_assign_division_id_foreign');
            $table->unsignedBigInteger('division_id')->nullable()->change();
            $table->foreign('division_id')->references('id')->on('company_division')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_division', function (Blueprint $table) {
            //
        });
    }
}
