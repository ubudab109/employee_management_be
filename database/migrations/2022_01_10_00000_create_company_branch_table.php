<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_branch', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name');
            $table->string('branch_code')->nullable();
            $table->integer('branch_order')->nullable();
            $table->boolean('is_centered')->default(false);

            $table->char('province_id',2)->nullable();
            $table->foreign('province_id')
                ->references('id')
                ->on('provinces')
                ->onUpdate('cascade')->nullOnDelete();


            $table->char('regency_id', 4)->nullable();
            $table->foreign('regency_id')
                ->references('id')
                ->on('regencies')
                ->onUpdate('cascade')->nullOnDelete();

            $table->char('district_id', 7)->nullable();
            $table->foreign('district_id')
                ->references('id')
                ->on('districts')
                ->onUpdate('cascade')->nullOnDelete();

            $table->char('villages_id', 10)->nullable();
            $table->foreign('villages_id')
                ->references('id')
                ->on('villages')
                ->onUpdate('cascade')->nullOnDelete();

            $table->text('address')->nullable();

            $table->enum('status',['0','1'])->default('1')->comment('0: active, 1: inactive');
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();
            $table->float('attendance_radius')->nullable();
            $table->enum('work_type',['hybrid','wfo','remote'])->default('wfo');
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
        Schema::dropIfExists('company_branch');
    }
}
