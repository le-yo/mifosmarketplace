<?php

namespace Modules\MifosSms\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MifosSmsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call("Modules\MifosSms\Database\Seeders\SeedMifosSmsGatewaysTableSeeder");
        $this->call("Modules\MifosSms\Database\Seeders\SeedMifosSmsConfigsTableSeeder");
        $this->call("Modules\MifosSms\Database\Seeders\SeedHookNotificationTableSeeder");
    }
}
