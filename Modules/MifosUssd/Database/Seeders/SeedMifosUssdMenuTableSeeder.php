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
                'confirmation_message' => "Your account has been validated and your PIN has been set successfully. Kindly dial *665*300# to proceed",
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
                'is_root' => 2,
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
                'type' => 4,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 7
            array(
                'app_id' => 1,
                'title' => 'Apply Loan',
                'description' => 'Apply Loan Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 8
            array(
                'app_id' => 1,
                'title' => 'Select Loan to Repay',
                'description' => 'Repay Loan Menu'.PHP_EOL."Paybill 4017901",
                'is_root' => 0,
                'type' => 4,
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
                'type' => 4,
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
                'type' => 4,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 11
            array(
                'app_id' => 1,
                'title' => 'Shares',
                'description' => 'Shares Menu',
                'is_root' => 0,
                'type' => 4,
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
            //Menu 13
            array(
                'app_id' => 1,
                'title' => 'Apply Nibebe Loan',
                'description' => 'Apply Nibebe Loan',
                'is_root' => 0,
                'type' => 3,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "You loan has been submitted successfully. ",
            ),
            //Menu 14
            array(
                'app_id' => 1,
                'title' => 'Apply Masaa Loan',
                'description' => 'Apply Masaa Loan',
                'is_root' => 0,
                'type' => 3,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "You loan has been submitted successfully. ",
            ),
            //Menu 15
            array(
                'app_id' => 1,
                'title' => 'Logout',
                'description' => 'Logout',
                'is_root' => 0,
                'type' => 5,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "Thank you for using USSD",
            ),
            //Menu 16
            array(
                'app_id' => 2,
                'title' => 'Welcome to PawaCredit',
                'description' => 'Register',
                'is_root' => 1,
                'type' => 2,
                'skippable'=>true,
                'next_mifos_ussd_menu_id'=>17,
                'confirmation_message' => "Your registration was successful",
            ),
            //Menu 17
            array(
                'app_id' => 2,
                'title' => 'Welcome to PawaCredit',
                'description' => 'Set PIN',
                'is_root' => 1,
                'type' => 2,
                'skippable'=>true,
                'next_mifos_ussd_menu_id'=>18,
                'confirmation_message' => "Your PIN was set successfully",
            ),
            //Menu 18
            array(
                'app_id' => 2,
                'title' => 'Welcome to PawaCredit',
                'description' => 'Set PIN',
                'is_root' => 1,
                'type' => 1,
                'skippable'=>true,
                'next_mifos_ussd_menu_id'=>18,
                'confirmation_message' => "Your PIN was set successfully",
            ),
            //Menu 19
            array(
                'app_id' => 2,
                'title' => 'Get PawaCredit',
                'description' => 'Get PawaCredit',
                'is_root' => 1,
                'type' => 2,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>18,
                'confirmation_message' => "Get PawaCredit",
//                'confirmation_message' => "Your Token of KES {amount} has been sent successfully. Kindly make payment by {due_date}",
            ),
            //Menu 20
            array(
                'app_id' => 2,
                'title' => 'Repay Loan',
                'description' => 'Repay Loan',
                'is_root' => 1,
                'type' => 2,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>18,
                'confirmation_message' => "Your have successfully repaid KES {amount}. Kindly make the pending payment of KES {balance}  by {due_date}",
            ),
            //Menu 21
            array(
                'app_id' => 3,
                'title' => 'Welcome to ICEA LION GROUP',
                'description' => 'Set PIN',
                'is_root' => 1,
                'type' => 1,
                'skippable'=>true,
                'next_mifos_ussd_menu_id'=>22,
                'confirmation_message' => "Your PIN was set successfully",
            ),
            //Menu 22
            array(
                'app_id' => 3,
                'title' => 'Please Choose a Company',
                'description' => 'ICEA',
                'is_root' => 1,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>22,
                'confirmation_message' => "Verification",
//                'confirmation_message' => "Your Token of KES {amount} has been sent successfully. Kindly make payment by {due_date}",
            ),
            //Menu 23
            array(
                'app_id' => 3,
                'title' => 'Create a profile',
                'description' => 'Register',
                'is_root' => 1,
                'type' => 2,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>22,
//                'confirmation_message' => "Verification",
                'confirmation_message' => "Welcome to ICEA LION. Your profile has been successfully created. Dial 665*9# to proceed to the main menu.",
            ),
            //Menu 24
            array(
                'app_id' => 3,
                'title' => 'Welcome to ICEA LION GROUP',
                'description' => 'Set PIN',
                'is_root' => 1,
                'type' => 1,
                'skippable'=>true,
                'next_mifos_ussd_menu_id'=>22,
                'confirmation_message' => "Your PIN was set successfully",
            ),
            //Menu 25
            array(
                'app_id' => 3,
                'title' => 'Registration',
                'description' => 'Set PIN',
                'is_root' => 1,
                'type' => 1,
                'skippable'=>true,
                'next_mifos_ussd_menu_id'=>22,
                'confirmation_message' => "Your PIN was set successfully",
            ),
            //Menu 26
            array(
                'app_id' => 2,
                'title' => 'Deposit',
                'description' => 'Deposit',
                'is_root' => 1,
                'type' => 2,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>18,
                'confirmation_message' => "Your have successfully Deposited KES {amount}.",
            ),
            //Menu 27
            array(
                'app_id' => 1,
                'title' => 'Apply Sikukuu Loan',
                'description' => 'Apply Sikukuu Loan',
                'is_root' => 0,
                'type' => 3,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "You loan has been submitted successfully. ",
            ),

            //Tresor APP
            //menu 28
            array(
                'app_id' => 5,
                'title' => 'Welcome to {app_name}',
                'description' => 'Process to Validate a first time user using ID',
                'is_root' => 1,
                'type' => 3,
                'skippable'=>true,
                'next_mifos_ussd_menu_id'=>29,
                'confirmation_message' => "Your account has been validated and your PIN has been set successfully. Kindly dial {app_ussd} to proceed",
            ),
            //Menu 29
            array(
                'app_id' => 5,
                'title' => 'Welcome to {app_name}',
                'description' => 'Authenticate User with PIN',
                'is_root' => 0,
                'type' => 2,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "PIN is correct. Proceed to Main Menu",
            ),
//            //Menu 30
            array(
                'app_id' => 5,
                'title' => 'Welcome to {app_name}',
                'description' => 'Home Menu',
                'is_root' => 2,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
//            //Menu 31
            array(
                'app_id' => 5,
                'title' => 'Loans',
                'description' => 'Loans Menu',
                'is_root' => 0,
                'type' => 1, //Get and display loan products
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
//            //Menu 32
            array(
                'app_id' => 5,
                'title' => 'Deposits',
                'description' => 'Deposits Menu',
                'is_root' => 0,
                'type' => 1,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
//            //Menu 33
            array(
                'app_id' => 5,
                'title' => 'Apply Loan',
                'description' => 'Apply Loan Menu',
                'is_root' => 0,
                'type' => 6,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 34
            array(
                'app_id' => 5,
                'title' => 'Select Loan to Repay',
                'description' => 'Repay Loan Menu',
                'is_root' => 0,
                'type' => 7,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 35 //check balance
            array(
                'app_id' => 5,
                'title' => 'Loan Balance:',
                'description' => 'Repay Loan Menu',
                'is_root' => 0,
                'type' => 8,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 36 //Deposit from M-PESA
            array(
                'app_id' => 5,
                'title' => 'Deposit from M-PESA:',
                'description' => 'Deposit from M-PESA',
                'is_root' => 0,
                'type' => 9,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 37 //Deposit from M-PESA
            array(
                'app_id' => 5,
                'title' => 'Thank you for using {app_name} USSD',
                'description' => 'Logout',
                'is_root' => 0,
                'type' => 10,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
            //Menu 38 //Check balance
            array(
                'app_id' => 5,
                'title' => 'Deposit Balance',
                'description' => 'Logout',
                'is_root' => 0,
                'type' => 11,
                'skippable'=>false,
                'next_mifos_ussd_menu_id'=>0,
                'confirmation_message' => "",
            ),
//            //Menu 9
//            array(
//                'app_id' => 1,
//                'title' => 'Check Balance',
//                'description' => 'Check balance Menu',
//                'is_root' => 0,
//                'type' => 4,
//                'skippable'=>false,
//                'next_mifos_ussd_menu_id'=>0,
//                'confirmation_message' => "",
//            ),
//            //Menu 10
//            array(
//                'app_id' => 1,
//                'title' => 'Savings',
//                'description' => 'Savings Menu',
//                'is_root' => 0,
//                'type' => 4,
//                'skippable'=>false,
//                'next_mifos_ussd_menu_id'=>0,
//                'confirmation_message' => "",
//            ),
//            //Menu 11
//            array(
//                'app_id' => 1,
//                'title' => 'Shares',
//                'description' => 'Shares Menu',
//                'is_root' => 0,
//                'type' => 4,
//                'skippable'=>false,
//                'next_mifos_ussd_menu_id'=>0,
//                'confirmation_message' => "",
//            ),
//            //Menu 12
//            array(
//                'app_id' => 1,
//                'title' => 'Reset Your PIN',
//                'description' => 'Process to Reset User PIN',
//                'is_root' => 0,
//                'type' => 3,
//                'skippable'=>false,
//                'next_mifos_ussd_menu_id'=>0,
//                'confirmation_message' => "You have Reset Your PIN Successfully. Dial *665*300# to proceed",
//            ),
//            //Menu 13
//            array(
//                'app_id' => 1,
//                'title' => 'Apply Nibebe Loan',
//                'description' => 'Apply Nibebe Loan',
//                'is_root' => 0,
//                'type' => 3,
//                'skippable'=>false,
//                'next_mifos_ussd_menu_id'=>0,
//                'confirmation_message' => "You loan has been submitted successfully. ",
//            ),
//            //Menu 14
//            array(
//                'app_id' => 1,
//                'title' => 'Apply Masaa Loan',
//                'description' => 'Apply Masaa Loan',
//                'is_root' => 0,
//                'type' => 3,
//                'skippable'=>false,
//                'next_mifos_ussd_menu_id'=>0,
//                'confirmation_message' => "You loan has been submitted successfully. ",
//            ),
//            //Menu 15
//            array(
//                'app_id' => 1,
//                'title' => 'Logout',
//                'description' => 'Logout',
//                'is_root' => 0,
//                'type' => 5,
//                'skippable'=>false,
//                'next_mifos_ussd_menu_id'=>0,
//                'confirmation_message' => "Thank you for using Hazina USSD",
//            ),
//
        ));
    }
}
