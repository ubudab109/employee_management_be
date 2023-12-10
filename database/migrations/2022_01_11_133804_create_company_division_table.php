<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDivisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_division', function (Blueprint $table) {
            $table->id();
            $table->string('division_code')->nullable()->unique();
            $table->foreignId('branch_id')->constrained('company_branch')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('division_name');
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
        Schema::dropIfExists('company_division');
    }
}
