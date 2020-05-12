<?php

namespace Modules\MifosInstance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class MifosInstanceConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('mifos_instance_configs')->truncate();

        DB::table('mifos_instance_configs')->delete();

        DB::table('mifos_instance_configs')->insert(array(
            array(
                'name' => "Pesapal",
                'slug' => "pesapal",
                'mifos_url' => "https://hermes.pesapal.credit/",
                'username' => "Pesapal",
                'password' => Crypt::encrypt('P3s@Cr7dt'),
                'tenant' => "default",
            ),
            array(
                'name' => 'nyota',
                'slug' => 'nyota',
                'mifos_url' => "https://nyota.mifosconnect.com/",
                'username' => "api",
                'password' => Crypt::encrypt('ggdicxwkvrspo'),
                'tenant' => "nyota",
            ),
            array(
                'name' => 'Hazina',
                'slug' => 'hazina',
                'mifos_url' => "https://hazinatrust.mifosconnect.com/",
//                'mifos_url' => "https://hazinademo.mifosconnect.com/",
                'username' => "API",
                'password' => Crypt::encrypt('ap!@dm1N'),
//                'password' => Crypt::encrypt('API@123'),
                'tenant' => "hazinatrust",
//                'tenant' => "hazinademo",
            ),
            array(
                'name' => 'hazinademo',
                'slug' => 'hazinademo',
                'mifos_url' => "https://hazinademo.mifosconnect.com/",
                'username' => "API",
                'password' => Crypt::encrypt('API@123'),
                'tenant' => "hazinademo",
            ),
        ));
    }
}
