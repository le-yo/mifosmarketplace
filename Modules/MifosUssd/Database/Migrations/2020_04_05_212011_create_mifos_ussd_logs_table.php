<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMifosUssdLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mifos_ussd_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id')->default(0);
            $table->string('phone');
            $table->string('text')->nullable();
            $table->string('service_code')->nullable();
            $table->string('session_id')->nullable();
            $table->integer('type')->nullable();
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
        Schema::dropIfExists('mifos_ussd_logs');
    }
}
