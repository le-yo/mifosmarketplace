<?php

namespace Modules\MifosReminder\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MifosReminderTableSeeder extends Seeder
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
        DB::table('mifos_reminders')->truncate();

        DB::table('mifos_reminders')->delete();

        DB::table('mifos_reminders')->insert(array(
            array(
                'mifos_reminder_app_id' => 1,
                'message' => "Dear {name}, your loan repayment of KES {amount} is due on {due_date}. Please make plans to make payment. Thanks",
                'day' => "-3",
            )
        ));
    }
}
