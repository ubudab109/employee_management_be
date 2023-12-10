<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnColorDivisionToCompanyDivisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_division', function (Blueprint $table) {
            $table->string('style_color')->default('#F79E1B')->after('division_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_division', function (Blueprint $table) {
            $table->dropColumn('style_color');
        });
    }
}
