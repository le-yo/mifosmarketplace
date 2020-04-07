<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMifosUssdConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mifos_ussd_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_id')->default(0);
            $table->string('app_name')->default(0);
            $table->string('mifos_url')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('tenant')->nullable();
            $table->string('ussd_gateway_id')->default(1);
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
        Schema::dropIfExists('mifos_ussd_configs');
    }
}
