<?php

namespace Modules\MifosSms\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeedHookNotificationTableSeeder extends Seeder
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
        DB::table('hook_notifications')->truncate();

        DB::table('hook_notifications')->delete();

        DB::table('hook_notifications')->insert(array(
            array(
                'app_id' => 3,
                'is_active' => 1,
                'entity_name' => "loanStatusType.approved",
                'action_name' => "approved",
                'message' => "Dear {first_name}, your loan application {account_id} has been approved and is awaiting disbursement on your next meeting day. For cancellation, please call your branch manager or our hotlines 0706247815 / 0784247815 before your next meeting day.",
            ),
            array(
                'app_id' => 3,
                'is_active' => 1,
                'entity_name' => "loanStatusType.rejected",
                'action_name' => "Rejected",
                'message' => "Dear {first_name}, your loan application {account_id} has been rejected. Please call your branch manager or our hotlines 0706247815 / 0784247815 for details.",
            ),
        ));
    }
}
