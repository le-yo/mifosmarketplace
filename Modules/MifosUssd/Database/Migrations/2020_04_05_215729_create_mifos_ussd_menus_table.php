<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMifosUssdMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mifos_ussd_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id');
            $table->string('title')->nullable();
            $table->string('is_root')->default(0);
            $table->string('description')->nullable();
            $table->integer('type')->default(1);
            $table->boolean('skippable')->default(0);
            $table->integer('next_mifos_ussd_menu_id')->default(0);
            $table->string('confirmation_message')->nullable();
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
        Schema::dropIfExists('mifos_ussd_menus');
    }
}
