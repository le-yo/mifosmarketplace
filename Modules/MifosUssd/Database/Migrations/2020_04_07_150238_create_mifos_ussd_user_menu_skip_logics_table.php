<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMifosUssdUserMenuSkipLogicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mifos_ussd_user_menu_skip_logic', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone');
            $table->integer('mifos_ussd_menu_id');
            $table->boolean('skip')->default(false);
            $table->integer('next_mifos_ussd_menu_id')->default(0);
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
        Schema::dropIfExists('mifos_ussd_user_menu_skip_logic');
    }
}
