<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMifosRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mifos_reminders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mifos_reminder_app_id')->default(0);
            $table->longText('message');
            $table->string('day')->nullable();
            $table->string('schedule_time')->default(0);
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
        Schema::dropIfExists('mifos_reminders');
    }
}
