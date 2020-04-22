<?php

namespace Modules\MifosSms\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SeedMifosSmsConfigsTableSeeder extends Seeder
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
        DB::table('mifos_sms_configs')->truncate();

        DB::table('mifos_sms_configs')->delete();

        DB::table('mifos_sms_configs')->insert(array(
            array(
                'app_id' => 1,
                'gateway_id' => 2,
                'username' => "pesapalcredit",
                'key' => Crypt::encrypt('9d55d032eb07bee9025dc7d4bb4a1bfb4e3991dac7a4bfc1fbf439296298e1f4'),
                'sender_name' => "PESAPAL",
            ),
            array(
                'app_id' => 2,
                'gateway_id' => 1,
                'username' => "nyota",
                'key' => Crypt::encrypt('ue482g2BtYHMLNIz0m81Wliyk83hhPPdIzU2SIkNLbHDyiEA3fWQUlDzIfpIJT4G'),
                'sender_name' => "NYOTA",
            ),
            array(
                'app_id' => 3,
                'gateway_id' => 3,
                'username' => "itld-hazina",
                'key' => Crypt::encrypt('H4z1na5T'),
                'sender_name' => "HAZINAGROUP",
            ),
            array(
                'app_id' => 4,
                'gateway_id' => 1,
                'username' => "helaplus",
                'key' => Crypt::encrypt('rICdLH73o7OTfprEWldpyCHTtHRfMK5661J2XIZ0pnjiajv1Sf1zZBOAUoXrMmwt'),
                'sender_name' => "HELAPLUS",
            ),
        ));
    }
}
