<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table ){
            $table->dropColumn('name');
            $table->dropColumn('address');
            $table->dropColumn('job_status');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('firstname')->after('uuid');
            $table->string('lastname')->nullable()->after('firstname');
            $table->string('mobile_phone')->after('phone_number')->nullable();
            $table->string('pob')->after('password')->nullable()->comment('place of birth');
            $table->date('dob')->after('pob')->comment('date of birth');
            $table->string('marital_status')->after('dob')->nullable();
            $table->string('blood_type')->after('marital_status')->nullable();
            $table->string('identity_type')->after('blood_type')->nullable();
            $table->string('identity_number')->after('identity_type')->nullable();
            $table->text('citizent_address')->after('dob')->nullable();
            $table->text('resident_address')->after('citizent_address')->nullable();
            $table->string('postal_code')->after('resident_address')->nullable();
            $table->date('join_date')->after('postal_code');
            $table->date('end_date')->nullable()->after('join_date');
            $table->string('job_status')->after('end_date')->nullable();
            $table->string('job_level')->after('job_status')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
