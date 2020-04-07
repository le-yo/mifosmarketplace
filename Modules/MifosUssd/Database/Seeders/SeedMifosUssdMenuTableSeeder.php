<?php

namespace Modules\MifosUssd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeedMifosUssdMenuTableSeeder extends Seeder
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
        DB::table('mifos_ussd_menus')->truncate();

        DB::table('mifos_ussd_menus')->delete();

        DB::table('mifos_ussd_menus')->insert(array(
            //menu 1
            array(
                'app_id' => 1,
                'title' => 'Welcome to Hazina',
                'description' => 'Process to Validate a first time user using ID',
                'is_root' => 1,
                'type' => 3,
                'skippable'=>true,
                'next_mifos_ussd_menu_id'=>2,
                'confirmation_message' => "Your account has been validate and your PIN has been set successfully. Kindly dial *665*300# to proceed",
            ),
            //Menu 2
            array(
                'app_id' => 1,
                'title' => 'Welcome to Hazina',
                'description' => 'Authenticate User with PIN',
                'is_root' => 0,
                'type' => 2,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "PIN is correct. Proceed to Main Menu",
            ),
            //Menu 3
            array(
                'app_id' => 1,
                'title' => 'Welcome to Hazina',
                'description' => 'Hazina Home Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 4
            array(
                'app_id' => 1,
                'title' => 'Loans',
                'description' => 'Loans Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 5
            array(
                'app_id' => 1,
                'title' => 'Deposits',
                'description' => 'Deposits Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 6
            array(
                'app_id' => 1,
                'title' => 'Fees',
                'description' => 'Fees Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 7
            array(
                'app_id' => 1,
                'title' => 'Apply Loan',
                'description' => 'Apply Lon Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 8
            array(
                'app_id' => 1,
                'title' => 'Repay Loan',
                'description' => 'Repay Loan Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 9
            array(
                'app_id' => 1,
                'title' => 'Check Balance',
                'description' => 'Check balance Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 10
            array(
                'app_id' => 1,
                'title' => 'Savings',
                'description' => 'Savings Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 11
            array(
                'app_id' => 1,
                'title' => 'Savings',
                'description' => 'Shares Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 12
            array(
                'app_id' => 1,
                'title' => 'Reset Your PIN',
                'description' => 'Process to Reset User PIN',
                'is_root' => 0,
                'type' => 3,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "You have Reset Your PIN Successfully. Dial *665*300# to proceed",
            ),
        ));
    }
}
