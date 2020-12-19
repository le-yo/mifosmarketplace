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
                'day' => "3",
                'schedule_time' => "09:00",
            ),
            array(
                'mifos_reminder_app_id' => 1,
                'message' => "Dear {name}, your loan repayment of KES {amount} is due on {due_date}. Please make plans to make payment. Thanks",
                'day' => "3",
                'schedule_time' => "16:00",
            ),
            array(
                'mifos_reminder_app_id' => 2,
                'message' => "Dear {name}, your loan is due today. Kindly pay {amount} before 12 noon through Paybill 189779 and Account {prefix}{external_id}.",
                'day' => "0",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 2,
                'message' => "Dear {name}, your loan payment is late. Kindly pay {amount} immediately to avoid penalties and possible denial of future loans. Paybill 189779 Account {prefix}{external_id}",
                'day' => "-1",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan will fall due tomorrow. Kindly pay {amount} by {due_date} to avoid additional charges. Paybill 4034061 Account {external_id}.",
                'day' => "1",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan payment of {amount} has been overdue for 1 day. Kindly pay immediately to avoid additional charges and possible denial of future loans. Paybill 4034061 Account {external_id}.",
                'day' => "-1",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan payment of {amount} has been overdue for 7 days. Kindly pay immediately to avoid additional charges and possible denial of future loans. Paybill 4034061 Account {external_id}.",
                'day' => "-7",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan payment of {amount} has been overdue for 14 day. Kindly pay immediately to avoid additional charges and possible denial of future loans. Paybill 4034061 Account {external_id}.",
                'day' => "-14",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan payment of {amount} has been overdue for 21 day. Kindly pay immediately to avoid additional charges and possible denial of future loans. Paybill 4034061 Account {external_id}.",
                'day' => "-21",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan payment of {amount} has been overdue for 28 day. Kindly pay immediately to avoid additional charges and possible denial of future loans. Paybill 4034061 Account {external_id}.",
                'day' => "-28",
                'schedule_time' => "08:00",
            ), 
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan payment of {amount} has been overdue for 35 day. Kindly pay immediately to avoid additional charges and possible denial of future loans. Paybill 4034061 Account {external_id}.",
                'day' => "-35",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan payment of {amount} has been overdue for 42 day. Kindly pay immediately to avoid additional charges and possible denial of future loans. Paybill 4034061 Account {external_id}.",
                'day' => "-42",
                'schedule_time' => "08:00",
            ),
            array(
                'mifos_reminder_app_id' => 3,
                'message' => "Dear {name}, your loan payment of {amount} has been overdue for 49 day. Kindly pay immediately to avoid additional charges and possible denial of future loans. Paybill 4034061 Account {external_id}.",
                'day' => "-49",
                'schedule_time' => "08:00",
            )
        ));
    }
}
