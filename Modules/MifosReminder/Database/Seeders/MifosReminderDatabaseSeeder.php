<?php

namespace Modules\MifosReminder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MifosReminderDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

         $this->call("Modules\MifosReminder\Database\Seeders\MifosReminderTableSeeder");
         $this->call("Modules\MifosReminder\Database\Seeders\MifosReminderConfigTableSeeder");
         $this->call("Modules\MifosReminder\Database\Seeders\MifosReminderSmsGatewayTableSeeder");
    }
}
