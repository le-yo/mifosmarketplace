<?php

namespace Modules\MifosUssd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeedMifosUssdGatewaysTableSeeder extends Seeder
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
        DB::table('mifos_ussd_gateways')->truncate();

        DB::table('mifos_ussd_gateways')->delete();

        DB::table('mifos_ussd_gateways')->insert(array(
            array(
                'name' => 'Wasiliana',
            ),
            array(
                'name' => 'Africastalking',
            ),
        ));
    }
}
