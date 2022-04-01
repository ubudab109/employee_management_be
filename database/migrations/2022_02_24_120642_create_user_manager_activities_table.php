<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserManagerActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_manager_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_manager_id')->constrained('user_manager', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->text('activities');
            $table->date('date');
            $table->time('time');
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
        Schema::dropIfExists('user_manager_activities');
    }
}
