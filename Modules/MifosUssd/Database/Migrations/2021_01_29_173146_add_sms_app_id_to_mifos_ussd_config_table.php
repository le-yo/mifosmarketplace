<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmsAppIdToMifosUssdConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mifos_ussd_configs', function (Blueprint $table) {
            $table->string('sms_app_id')->after('ussd_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mifos_ussd_configs', function (Blueprint $table) {
            $table->dropColumn('sms_app_id');
        });
    }
}
