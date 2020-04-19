<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHookNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hook_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('app_id');
            $table->string('name')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('content_type')->default('json');
            $table->string('url')->nullable();
            $table->string('entity_name');
            $table->string('action_name');
            $table->text('message');
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
        Schema::dropIfExists('hook_notifications');
    }
}
