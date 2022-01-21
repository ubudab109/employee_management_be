<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotedDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_noted_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('noted_id');
            $table->string('color')->nullable();
            $table->time('time');
            $table->text('note');
            $table->timestamps();

            $table->foreign('noted_id')->references('id')->on('user_noted')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_noted_data');
    }
}
