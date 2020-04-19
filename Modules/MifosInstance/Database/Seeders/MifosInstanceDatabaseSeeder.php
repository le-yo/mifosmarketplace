<?php

namespace Modules\MifosInstance\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MifosInstanceDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $this->call("Modules\MifosInstance\Database\Seeders\MifosInstanceConfigTableSeeder");
    }
}
