<?php

namespace Modules\MifosUssd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeedMifosUssdSettingsTableSeeder extends Seeder
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
        DB::table('mifos_ussd_settings')->truncate();

        DB::table('mifos_ussd_settings')->delete();

        DB::table('mifos_ussd_settings')->insert(array(
            array(
                'slug' => 'no_app_found',
                'value' => 'Error::Application Is not Yet configured',
            ),

        ));
    }
}
