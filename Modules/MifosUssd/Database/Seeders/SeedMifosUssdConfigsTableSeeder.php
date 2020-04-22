<?php

namespace Modules\MifosUssd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SeedMifosUssdConfigsTableSeeder extends Seeder
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
        DB::table('mifos_ussd_configs')->truncate();

        DB::table('mifos_ussd_configs')->delete();

        DB::table('mifos_ussd_configs')->insert(array(
            array(
                'app_id' => 1,
                'app_name' => 'hazina',
                'mifos_url' => "https://hazinatrust.mifosconnect.com/",
                'username' => "API",
                'password' => Crypt::encrypt('ap!@dm1N'),
                'tenant' => "hazinatrust",
                'ussd_gateway_id' => 1,
            ),
            array(
                'app_id' => 2,
                'app_name' => 'pawacredit',
                'mifos_url' => "https://hazinademo.mifosconnect.com/",
                'username' => "API",
                'password' => Crypt::encrypt('ap!@dm1N'),
                'tenant' => "hazinatrust",
                'ussd_gateway_id' => 1,
            ),
        ));
    }
}
