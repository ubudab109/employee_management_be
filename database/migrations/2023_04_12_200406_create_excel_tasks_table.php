<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExcelTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('excel_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('company_division')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('manager_id')->constrained('user_manager')->cascadeOnDelete()->cascadeOnUpdate();
            $table->nullableMorphs('source');
            $table->enum('type', ['export', 'import'])->nullable();
            $table->string('download')->nullable();
            $table->string('message')->nullable();
            $table->json('settings')->nullable();
            $table->enum('status', ['pending', 'processing', 'finished', 'failed'])->default('pending');
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
        Schema::dropIfExists('excel_tasks');
    }
}
