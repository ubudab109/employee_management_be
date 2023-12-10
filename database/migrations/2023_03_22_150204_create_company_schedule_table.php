<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_schedule', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('company_division')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('code')->unique()->nullable();
            $table->boolean('is_default')->default(0);
            $table->string('name');
            $table->time('clock_in');
            $table->time('clock_out');
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('map_url')->nullable();
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
        Schema::dropIfExists('company_schedule');
    }
}
