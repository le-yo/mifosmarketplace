<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMifosUssdSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mifos_ussd_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone')->nullable();
            $table->integer('app_id')->default(0);
            $table->integer('session')->default(0);
            $table->integer('progress')->default(0);
            $table->integer('menu_id')->default(0);
            $table->integer('menu_item_id')->default(0);
            $table->integer('confirm_from')->default(0);
            $table->integer('difficulty_level')->default(0);
            $table->longText('other')->nullable();
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
        Schema::dropIfExists('mifos_ussd_sessions');
    }
}
