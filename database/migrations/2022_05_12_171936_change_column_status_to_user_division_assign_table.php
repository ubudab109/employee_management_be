<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnStatusToUserDivisionAssignTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_division_assign', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('user_division_assign', function (Blueprint $table) {
            $table->enum('status', ['0','1', '2'])->after('division_id')->comment('0: inactive, 1 : active, 2 : pending')->default('2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_division_assign', function (Blueprint $table) {
            //
        });
    }
}
