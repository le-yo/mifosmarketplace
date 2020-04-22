<?php

namespace Modules\MifosUssd\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SeedMifosUssdMenuItemsTableSeeder extends Seeder
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
        DB::table('mifos_ussd_menu_items')->truncate();

        DB::table('mifos_ussd_menu_items')->delete();

        DB::table('mifos_ussd_menu_items')->insert(array(
            array(
                'menu_id' => 1,
                'description' => 'Enter Your National ID',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => 'custom',
                'confirmation_phrase' => 'IGNORE',
            ),
            array(
                'menu_id' => 1,
                'description' => 'Set PIN: Enter your 4 digit PIN',
                'next_menu_id' => 0,
                'step' => 2,
                'validation' => 'min:4|max:4',
                'confirmation_phrase' => 'PIN',
            ),
            array(
                'menu_id' => 1,
                'description' => 'Confirm your 4 digit PIN',
                'next_menu_id' => 0,
                'step' => 3,
                'validation' => 'custom',
                'confirmation_phrase' => 'PIN',
            ),

            array(
                'menu_id' => 2,
                'description' => 'Enter PIN ((Forgot PIN? Answer with 0)',
                'next_menu_id' => 2,
                'step' => 1,
                'validation' => 'custom',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 12,
                'description' => 'Enter Your National ID',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => 'custom',
                'confirmation_phrase' => 'IGNORE',
            ),
            array(
                'menu_id' => 12,
                'description' => 'Set PIN: Enter your 4 digit PIN',
                'next_menu_id' => 0,
                'step' => 2,
                'validation' => 'min:4|max:4',
                'confirmation_phrase' => 'PIN',
            ),
            array(
                'menu_id' => 12,
                'description' => 'Confirm your 4 digit PIN',
                'next_menu_id' => 0,
                'step' => 3,
                'validation' => 'custom',
                'confirmation_phrase' => 'PIN',
            ),
            array(
                'menu_id' => 12,
                'description' => 'Confirm your 4 digit PIN',
                'next_menu_id' => 0,
                'step' => 3,
                'validation' => 'custom',
                'confirmation_phrase' => 'PIN',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Loans',
                'next_menu_id' => 4,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Deposit',
                'next_menu_id' => 5,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Fees',
                'next_menu_id' => 6,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 3,
                'description' => 'Logout',
                'next_menu_id' => 15,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 4,
                'description' => 'Apply Loan',
                'next_menu_id' => 7,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 4,
                'description' => 'Repay Loan',
                'next_menu_id' => 8,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 4,
                'description' => 'Check Balance',
                'next_menu_id' => 9,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 4,
                'description' => 'Main Menu',
                'next_menu_id' => 3,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 4,
                'description' => 'Logout',
                'next_menu_id' => 15,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 7,
                'description' => 'Nibebe Loan',
                'next_menu_id' => 13,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 7,
                'description' => 'Masaa Loan',
                'next_menu_id' => 14,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 7,
                'description' => 'Back to Loans',
                'next_menu_id' => 4,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 7,
                'description' => 'Main Menu',
                'next_menu_id' => 3,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 7,
                'description' => 'Logout',
                'next_menu_id' => 15,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 5,
                'description' => 'Savings',
                'next_menu_id' => 10,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 5,
                'description' => 'Shares',
                'next_menu_id' => 11,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 5,
                'description' => 'Check Balance',
                'next_menu_id' => 4,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 5,
                'description' => 'Main Menu',
                'next_menu_id' => 3,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 5,
                'description' => 'Logout',
                'next_menu_id' => 15,
                'step' => 0,
                'validation' => '',
                'confirmation_phrase' => '',
            ),
            array(
                'menu_id' => 13,
                'description' => 'Enter Amount',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => 'schedule',
                'confirmation_phrase' => 'Amount',
            ),
            array(
                'menu_id' => 14,
                'description' => 'Enter Amount',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => 'schedule',
                'confirmation_phrase' => 'Amount',
            ),
            //pawa credit registration
            array(
                'menu_id' => 16,
                'description' => 'Enter your name',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => '',
                'confirmation_phrase' => 'Name',
            ),
            array(
                'menu_id' => 16,
                'description' => 'Enter your National ID',
                'next_menu_id' => 0,
                'step' => 2,
                'validation' => '',
                'confirmation_phrase' => 'ID',
            ),
            array(
                'menu_id' => 16,
                'description' => 'Do you Accept Terms and Conditions?'.PHP_EOL.'1. Yes'.PHP_EOL.'2. No',
                'next_menu_id' => 0,
                'step' => 3,
                'validation' => '',
                'confirmation_phrase' => 'IGNORE',
            ),
            array(
                'menu_id' => 17,
                'description' => 'Set PIN',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => '',
                'confirmation_phrase' => 'PIN',
            ),
            array(
                'menu_id' => 17,
                'description' => 'Confirm PIN',
                'next_menu_id' => 0,
                'step' => 2,
                'validation' => 'custom',
                'confirmation_phrase' => 'IGNORE',
            ),
            array(
                'menu_id' => 18,
                'description' => 'Get Pawa Credit',
                'next_menu_id' => 19,
                'step' => 0,
                'validation' => 'custom',
                'confirmation_phrase' => 'IGNORE',
            ),
            array(
                'menu_id' => 18,
                'description' => 'Repay',
                'next_menu_id' => 19,
                'step' => 0,
                'validation' => 'custom',
                'confirmation_phrase' => 'IGNORE',
            ),
            array(
                'menu_id' => 18,
                'description' => 'My Account',
                'next_menu_id' => 19,
                'step' => 0,
                'validation' => 'custom',
                'confirmation_phrase' => 'IGNORE',
            ),
            array(
                'menu_id' => 19,
                'description' => 'You qualify for up to KES 100 of PawaCredit tokens. '.PHP_EOL.'Enter Amount',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => '',
                'confirmation_phrase' => 'Amount',
            ),
            array(
                'menu_id' => 19,
                'description' => 'Enter Meter Number',
                'next_menu_id' => 0,
                'step' => 2,
                'validation' => '',
                'confirmation_phrase' => 'Meter Number',
            ),
            array(
                'menu_id' => 20,
                'description' => 'Enter Amount',
                'next_menu_id' => 0,
                'step' => 1,
                'validation' => 'custom',
                'confirmation_phrase' => 'Amount',
            ),
        ));
    }
}
