<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppUssdToMifosUssdConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mifos_ussd_configs', function (Blueprint $table) {
            $table->string("ussd_code")->nullable();
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
            $table->dropColumn("ussd_code");
        });
    }
}
