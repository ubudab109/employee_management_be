<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claim_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('company_division')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->double('max_claim')->default(0);
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
        Schema::dropIfExists('claim_type');
    }
}
