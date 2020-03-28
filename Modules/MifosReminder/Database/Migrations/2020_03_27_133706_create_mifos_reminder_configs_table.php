<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMifosReminderConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mifos_reminder_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('mifos_reminder_app_id')->default(0);
            $table->string('mifos_url')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('tenant')->nullable();
            $table->string('mifos_reminder_sms_gateway_id')->default(1);
            $table->string('sms_username')->nullable();
            $table->longText('sms_key')->nullable();
            $table->longText('sender_name')->nullable();
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
        Schema::dropIfExists('mifos_reminder_configs');
    }
}
