<?php

namespace Modules\MifosReminder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class MifosReminderConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();
//        Eloquent::unguard();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('mifos_reminder_configs')->truncate();

        DB::table('mifos_reminder_configs')->delete();

        DB::table('mifos_reminder_configs')->insert(array(
            array(
                'mifos_reminder_app_id' => 1,
                'mifos_url' => "https://hermes.pesapal.credit/",
                'username' => "Pesapal",
                'password' => Crypt::encrypt('P3s@Cr7dt'),
                'tenant' => "default",
                'mifos_reminder_sms_gateway_id' => 2,
                'sms_username' => "pesapalcredit",
                'sms_key' => Crypt::encrypt('9d55d032eb07bee9025dc7d4bb4a1bfb4e3991dac7a4bfc1fbf439296298e1f4'),
                'sender_name' => "PESAPAL",
            ),
            array(
                'mifos_reminder_app_id' => 2,
                'mifos_url' => "https://nyota.mifosconnect.com/",
                'username' => "api",
                'password' => Crypt::encrypt('ggdicxwkvrspo'),
                'tenant' => "nyota",
                'mifos_reminder_sms_gateway_id' => 1,
                'sms_username' => "nyota",
                'sms_key' => Crypt::encrypt('ue482g2BtYHMLNIz0m81Wliyk83hhPPdIzU2SIkNLbHDyiEA3fWQUlDzIfpIJT4G'),
                'sender_name' => "NYOTA",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'mifos_url' => "https://zawadimashinani.mifosconnect.com/",
                'username' => "API",
                'password' => Crypt::encrypt('Z@waPi'),
                'tenant' => "zawadimashinani",
                'mifos_reminder_sms_gateway_id' => 1,
                'sms_username' => "ZAWADILTD",
                'sms_key' => Crypt::encrypt('5aDgsnaKwVh8oQRUM9623jJZuEX0BGkzTknbRWUVYTrYEuofaLGwlNzOd2pFV3Xf'),
                'sender_name' => "ZAWADILTD",
            ),
        ));
    }
}
