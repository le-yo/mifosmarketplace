<?php

namespace Modules\MifosUssd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MifosUssdDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call("Modules\MifosUssd\Database\Seeders\SeedMifosUssdConfigsTableSeeder");
        $this->call("Modules\MifosUssd\Database\Seeders\SeedMifosUssdSettingsTableSeeder");
        $this->call("Modules\MifosUssd\Database\Seeders\SeedMifosUssdGatewaysTableSeeder");
        $this->call("Modules\MifosUssd\Database\Seeders\SeedMifosUssdMenuTableSeeder");
        $this->call("Modules\MifosUssd\Database\Seeders\SeedMifosUssdMenuItemsTableSeeder");
    }
}
