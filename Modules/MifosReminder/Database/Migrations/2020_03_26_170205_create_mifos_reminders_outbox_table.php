<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMifosRemindersOutboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mifos_reminders_outbox', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id')->default(0);
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->integer('status')->default(0);
            $table->integer('reminder_id')->default(0);
            $table->longText('content')->default(null);
            $table->text('cost')->nullable();
            $table->text('messageId')->nullable();
            $table->text('messageParts')->nullable();
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
        Schema::dropIfExists('mifos_reminders_outbox');
    }
}
