<?php

namespace Modules\MifosHelper\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Modules\MifosHelper\Entities\MifosRequestLog;
use Modules\MifosReminder\Entities\MifosReminderOutbox;
use Modules\MifosUssd\Entities\MifosUssdMenu;

class MifosHelperController extends Controller
{

    public static function checkNextInstallment($loanId)
    {
        $overdue_status = 0;
        // Get the url for retrieving the specific loan
        $url = MIFOS_URL . "/loans/" . $loanId . "?associations=repaymentSchedule&" . MIFOS_tenantIdentifier;

        // Get the loan details
        $loan = Hooks::MifosGetTransaction($url, $post_data = "");

        // Initialize empty array
        $items = [];

        // Grab the schedule periods
        $repaymentSchedulePeriods = $loan->repaymentSchedule->periods;

        // Loop through all the periods
        for ($i = 0; $i < count($repaymentSchedulePeriods); $i++)
        {
            // Push only the periods that have not been paid for
            if (array_key_exists('complete', $repaymentSchedulePeriods[$i]) && $repaymentSchedulePeriods[$i]->complete == false) {
                array_push($items, $repaymentSchedulePeriods[$i]);
            }
        }
        // Get the Dates
        $today = Carbon::now()->format('Y m d');
        $dueDate = Carbon::parse($items[0]->dueDate[0].'-'.$items[0]->dueDate[1].'-'.$items[0]->dueDate[2])->format('Y m d');

        // Initialize empty variables
        $repaymentScheduleNextDate = '';
        $repaymentScheduleNextInstallment = 0;

        // Check if due date has passed and add overdue charges
        if ($dueDate < $today)
        {
            $repaymentScheduleNextInstallment = $items[0]->totalOutstandingForPeriod;
            $repaymentScheduleNextDate = Carbon::parse($items[0]->dueDate[0].'-'.$items[0]->dueDate[1].'-'.$items[0]->dueDate[2])->format('j F Y');
            $overdue_status = 2;
        }
        elseif ($dueDate > $today)
        {
            $repaymentScheduleNextInstallment = $items[0]->totalOutstandingForPeriod;
            $repaymentScheduleNextDate = Carbon::parse($items[0]->dueDate[0].'-'.$items[0]->dueDate[1].'-'.$items[0]->dueDate[2])->format('j F Y');

        }
        elseif($dueDate == $today)
        {
            $repaymentScheduleNextInstallment = $items[0]->totalOutstandingForPeriod;
            $repaymentScheduleNextDate = Carbon::parse($items[0]->dueDate[0].'-'.$items[0]->dueDate[1].'-'.$items[0]->dueDate[2])->format('j F Y');
            $overdue_status = 1;
        }

        // Grab the schedule total outstanding(balance)
        $repaymentScheduleOutstanding = $loan->repaymentSchedule->totalOutstanding;

        // Store the data in a response
        $response = array(
            'balance' => $repaymentScheduleOutstanding,
            'next_date' => $repaymentScheduleNextDate,
            'next_installment' => $repaymentScheduleNextInstallment,
            'overdue_status' => $overdue_status
        );

        return $response;
    }

    public static function getLoan($loan_id,$config)
    {
        $url = $config->mifos_url . "fineract-provider/api/v1/loans/" . $loan_id . "?associations=repaymentSchedule&tenantIdentifier=" . $config->tenant;
        $loan = self::get($url,$config);
        return $loan;
    }

    public static function listAllDueAndOverdueClients($config,$reminder)
    {
        // Get the url for running the report
//        $getURl = $mifos_url."fineract-provider/api/v1/runreports/Loan%20Payments%20Due%20Report?";
        $getURl = $config->mifos_url."fineract-provider/api/v1/runreports/Loan%20Payments%20Due%20Report?R_startDate=".Carbon::today()->addDays($reminder->day)->format('Y-m-d')."&R_endDate=".Carbon::today()->addDays($reminder->day+1)->format('Y-m-d')."&R_officeId=1";

        // Send a GET request
        $reports = self::get($getURl,$config);

        // Collect the data into a collection
        $collection = collect($reports);

        // Pull the column headers and data
        $columns = $collection->pull('columnHeaders');
        $data = $collection->pull('data');

        // Initialize empty array
        $response = [];
        $columnHeaders = [];

        // Loop through the columns and get their names
        foreach ($columns as $column)
        {
            array_push($columnHeaders, $column->columnName);
        }

        // Loop through the data and combine the column headers
        foreach ($data as $row)
        {
            $rowData = array_combine($columnHeaders, $row->row);

            array_push($response, $rowData);
        }

        return $response;
    }

    public static function sendSmsViaAT($to,$message,$config){

        $data = ['phone' => $to, 'message' => $message];

        $gateway    = new AfricasTalkingGateway($config->sms_username, Crypt::decrypt($config->sms_key));

        try
        {
            $results = $gateway->sendMessage($to, $message,$config->sender_name);
        }
        catch ( AfricasTalkingGatewayException $e )
        {
			$result = $e->getMessage();
        }

        return $results;

    }

    public static function sendSmsViaWasiliana($to,$message,$config){

        $data = array();
        $data['recipients'] = array($to);
        $data['from'] = $config->sender_name;
        $data['message'] = $message;
        $url = 'https://api.wasiliana.com/api/v1/developer/sms/bulk/send/sms/request';
        $apiKey = "apiKey: ".Crypt::decrypt($config->sms_key);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                $apiKey)
        );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        curl_close($ch);
        $dt = ['slug' => 'send_sms_response', 'content' => $data];
        $response = json_decode($data);
        return $response;
    }

    public static function get($url,$config)
    {

        $data = ['slug' => 'mifos_get_request', 'content' => $url];
        //log request
//        Log::create($data);
        $client = new Client(['verify' => false]);
        $credentials = base64_encode($config->username.':'.Crypt::decrypt($config->password));

        try {
            $data = $client->get($url,
                [
                    'headers' => [
                        'Authorization' => 'Basic '.$credentials,
                        'Content-Type' => 'application/json',
                        'Fineract-Platform-TenantId' => $config->tenant
                    ]
                ]
            );

            $response = json_decode($data->getBody());
        } catch (BadResponseException $exception) {
            $response = $exception->getResponse()->getBody()->getContents();

        }
        $data = ['slug' => 'mifos_get_response', 'content' => \GuzzleHttp\json_encode($response)];
        //log request
//        Log::create($data);

        return $response;
    }

    public static function MifosGetTransaction($url,$post_data=null,$config){
        $data = ['slug' => 'mifos_get_request', 'content' => $url];
        //log request
        MifosRequestLog::create($data);
//        print_r($url);
//        exit;
        $post_data="";
        $ch = curl_init();
        $data = "";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, $config->username.':'.Crypt::decrypt($config->password));

//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        $dt = ['slug' => 'mifos_get_response', 'content' => $data];
        //log response
        MifosRequestLog::create($dt);
        curl_close($ch);
        $response = json_decode($data);
        return $response;
    }

    public static function MifosPostTransaction($url,$post_data,$config){
        $data = ['slug' => 'mifos_post_request', 'content' => $post_data];
        //log request
        MifosRequestLog::create($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_data))
        );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, $config->username.':'.Crypt::decrypt($config->password));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
//        print_r($data);exit;
        curl_close($ch);

        $dt = ['slug' => 'mifos_post_response', 'content' => $data];

        //log response
        MifosRequestLog::create($dt);

        $response = json_decode($data);

        return $response;
    }

    public static function MifosPutTransaction($url,$post_data,$config){
        $data = ['slug' => 'mifos_post_request', 'content' => $post_data];
        //log request
        MifosRequestLog::create($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($post_data))
        );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, $config->username.':'.Crypt::decrypt($config->password));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
//        print_r($data);exit;
        curl_close($ch);

        $dt = ['slug' => 'mifos_post_response', 'content' => $data];

        //log response
        MifosRequestLog::create($dt);

        $response = json_decode($data);

        return $response;
    }

    public static function getClientUsingPhone($phone,$config){
        $no = substr($phone,-9);
        $url = $config->mifos_url . "fineract-provider/api/v1/clients?sqlSearch=(c.mobile_no%20like%20%27" . $no . "%27)&tenantIdentifier=" . $config->tenant;

        // Get all clients
        $client = MifosHelperController::MifosGetTransaction($url, $post_data = '',$config);

        if ($client->totalFilteredRecords == 0) {
            $url = $config->mifos_url . "fineract-provider/api/v1/clients?sqlSearch=(c.mobile_no%20like%20%270" . $no . "%27)&tenantIdentifier=" . $config->tenant;

            // Get all clients
            $client = MifosHelperController::MifosGetTransaction($url, $post_data = '',$config);

            if ($client->totalFilteredRecords == 0) {
                $url = $config->mifos_url . "fineract-provider/api/v1/clients?sqlSearch=(c.mobile_no%20like%20%27254" . $no . "%27)&tenantIdentifier=" . $config->tenant;

                // Get all clients
                $client = MifosHelperController::MifosGetTransaction($url, $post_data = '',$config);
            }
        }

        $user = FALSE;
        if ($client->totalFilteredRecords > 0) {
            return $client->pageItems[0];
        } else {
           return FALSE;
        }
    }

    public static function getClientByNationalId($externalid,$config){
        $user = FALSE;
        $url =$config->mifos_url . "fineract-provider/api/v1/search?exactMatch=true&query=" . $externalid . "&resource=clientIdentifiers&tenantIdentifier=" .$config->tenant;
        // Get client
        $client = self::MifosGetTransaction($url, $post_data = '',$config);
        return $client;
    }

    public static function getClientBypPhone($phone,$config){
        $user = FALSE;
        $url =$config->mifos_url . "fineract-provider/api/v1/search?exactMatch=true&query=" . $phone . "&resource=client&tenantIdentifier=" .$config->tenant;
        // Get client
        $client = self::MifosGetTransaction($url, $post_data = '',$config);
        return $client;
    }

    public static function getClientByClientId($client_id,$config)
    {
        $url = $config->mifos_url . "fineract-provider/api/v1/clients/" . $client_id . "?tenantIdentifier=" . $config->tenant;
        $client = self::MifosGetTransaction($url,null,$config);
        return $client;
    }

    public static function setDatatable($datatable,$entityid,$json_data,$config,$update=0){
        $postURl = $config->mifos_url . "fineract-provider/api/v1/datatables/".$datatable."/" . $entityid . "?tenantIdentifier=" .$config->tenant;
        if($update == 1){
        return self::MifosPutTransaction($postURl, $json_data,$config);
        }else{
        return self::MifosPostTransaction($postURl, $json_data,$config);
        }
    }

    public static function getDatatable($datatable,$entityid,$config){
        $postURl = $config->mifos_url . "fineract-provider/api/v1/datatables/".$datatable."/" . $entityid . "?tenantIdentifier=" .$config->tenant;
            return self::MifosGetTransaction($postURl,$config);
    }

    public static function calculateFullRepaymentSchedule($clientId, $amount, $loanProductId, $repaymentPeriods)
    {
        $loan_settings = setting::where('productId', $loanProductId)->first();

        $date = Carbon::now()->format('d M Y');
        if(Carbon::now()->isWeekend()){
            if(Carbon::now()->isSaturday()){
                $disbursement_date = Carbon::now()->addDays(2)->format('d M Y');
            }else{
                $disbursement_date = Carbon::now()->addDays(1)->format('d M Y');

            }
        }else{
            $disbursement_date = Carbon::now()->format('d M Y');
        }

        if($loanProductId == PCL_ID)
        {
            $interest = self::getGroupInterestRate($clientId);
            $periods = $repaymentPeriods;
        } else
        {
            $interest = $loan_settings->interestRatePerPeriod;
            $periods = $loan_settings->numberOfRepayments;
        }
        $groupId = self::getUserGroupId($clientId);
        $user_group = self::getUserGroup($groupId);
        $calendarId = $user_group->collectionMeetingCalendar->id;

        $loan_data = [];
        $loan_data['dateFormat'] = 'dd MMMM yyyy';
        $loan_data['locale'] = 'en_GB';
        $loan_data['productId'] = PCL_ID;
        $loan_data['clientId'] = $clientId;
        $loan_data['principal'] = $amount;
        $loan_data['loanTermFrequency'] = $periods;
        $loan_data['loanTermFrequencyType'] = 2;
        $loan_data['loanType'] = 'jlg';
        $loan_data['numberOfRepayments'] = $periods;
        $loan_data['repaymentEvery'] = $loan_settings->repaymentEvery;
        $loan_data['repaymentFrequencyType'] = $loan_settings->repaymentFrequencyType;
        $loan_data['interestRatePerPeriod'] = $interest;
        $loan_data['interestRateFrequencyType'] = 2;
        $loan_data['interestCalculationPeriodType'] = $loan_settings->interestCalculationPeriodType;
        $loan_data['interestType'] = 1;
        $loan_data['groupId'] = $groupId;
        $loan_data['amortizationType'] = $loan_settings->amortizationType;
        $loan_data['expectedDisbursementDate'] = $disbursement_date;
        $loan_data['transactionProcessingStrategyId'] = 1;
        $loan_data['submittedOnDate'] = $date;
        $loan_data['submittedOnDate'] = $date;
        $loan_data['calendarId'] = $calendarId;
        $dData = array();
        $dData['expectedDisbursementDate'] = $disbursement_date;
        $dData['principal'] = $amount;
        $dData['approvedPrincipal'] = $amount;
        $loan_data['disbursementData'] = array();
        // Get the url for calculating the loan schedule
        $url = MIFOS_URL."/loans?command=calculateLoanSchedule&". MIFOS_tenantIdentifier;

        // Post to the url to receive the schedule as a response
        $loan = Hooks::MifosPostTransaction($url, json_encode($loan_data));

        // Initialize an empty array for the schedule
        $schedule = [];

        // Get the periods for the schedule
        $paymentPeriods = $loan->periods;

        $response = '';
        // Loop through all the periods
        for ($i = 0; $i < count($paymentPeriods); $i++)
        {
            // Push only the peroids that have not been paid for
            if (array_key_exists('daysInPeriod', $paymentPeriods[$i])) {
                $outstandingForPeriod = number_format($paymentPeriods[$i]->totalOutstandingForPeriod,2);
                $paymentDueDate = Carbon::parse($paymentPeriods[$i]->dueDate[0].'-'.$paymentPeriods[$i]->dueDate[1].'-'.$paymentPeriods[$i]->dueDate[2])->format('d-m-Y');
//                $paymentDueDate = $paymentPeriods[$i]->dueDate[2].'/'.$paymentPeriods[$i]->dueDate[1].'/'.$paymentPeriods[$i]->dueDate[0];
                $response = $response.$paymentDueDate." : Kshs. ".$outstandingForPeriod.PHP_EOL;
                array_push($schedule, $outstandingForPeriod);
            }
        }
        return $response;
    }

    public static function applyLoan($product_id,$client_id, $amount,$config){
        $linkAccountId = '';
        $groupId = self::getUserGroupId($client_id,$config);
        $user_group = self::getUserGroup($groupId,$config);
        $calendarId = $user_group->collectionMeetingCalendar->id;
        $groupMeetingDate = Carbon::parse(implode('-', $user_group->collectionMeetingCalendar->nextTenRecurringDates[0]))->format('d M Y');

        //get loan settings:
        $url = $config->mifos_url . "fineract-provider/api/v1/loanproducts/".$product_id."?tenantIdentifier=" .$config->tenant;
        $loanproduct = self::MifosGetTransaction($url,null,$config);

        //get clients savings account:
        $savingsAccounts = self::getClientSavingsAccounts($client_id,$config);
        foreach ($savingsAccounts as $sa){
            if($sa->shortProductName == 'TAC'){
                $linkAccountId = $sa->id;
                break;
            }
        }


        $repaymentPeriods = $loanproduct->minNumberOfRepayments;;

        $date = Carbon::now()->format('d M Y');

        if(Carbon::now()->isWeekend()){
            if(Carbon::now()->isSaturday()){
                $disbursement_date = Carbon::now()->addDays(2)->format('d M Y');
            }else{
                $disbursement_date = Carbon::now()->addDays(1)->format('d M Y');
            }
        }else{
            $disbursement_date = Carbon::now()->format('d M Y');
        }

        $loan_data = [];
        $loan_data['locale'] = 'en_GB';
        $loan_data['dateFormat'] = 'dd MMMM yyyy';
        $loan_data['clientId'] = $client_id;
        $loan_data['productId'] = $loanproduct->id;
        $loan_data['principal'] = $amount;
        $loan_data['fundId'] = $loanproduct->fundId;
        $loan_data['loanTermFrequency'] = $repaymentPeriods;
        $loan_data['loanTermFrequencyType'] = $loanproduct->repaymentFrequencyType->id; // 1
        $loan_data['loanType'] = 'jlg';
        $loan_data['numberOfRepayments'] = $repaymentPeriods;
        $loan_data['repaymentEvery'] = $loanproduct->repaymentEvery; // 2
        $loan_data['repaymentFrequencyType'] = $loanproduct->repaymentFrequencyType->id; //3
        $loan_data['interestRatePerPeriod'] = $loanproduct->interestRatePerPeriod;
        $loan_data['interestRateFrequencyType'] = $loanproduct->interestRateFrequencyType->id;
        $loan_data['amortizationType'] = $loanproduct->amortizationType->id; //4
        $loan_data['groupId'] = $groupId;
//        $loan_data['interestType'] = self::getInterestType($loan_settings->productId);
        $loan_data['interestType'] = $loanproduct->interestType->id;
        $loan_data['interestCalculationPeriodType'] = $loanproduct->interestCalculationPeriodType->id; //5
        $loan_data['allowPartialPeriodInterestCalcualtion'] = $loanproduct->allowPartialPeriodInterestCalcualtion;
        $loan_data['expectedDisbursementDate'] = $disbursement_date;
        $loan_data['transactionProcessingStrategyId'] = $loanproduct->transactionProcessingStrategyId; //6
        $loan_data['graceOnPrincipalPayment'] = $loanproduct->graceOnPrincipalPayment; //6
        $loan_data['graceOnInterestPayment'] = $loanproduct->graceOnInterestPayment; //6
        $loan_data['overdueDaysForNPA'] = $loanproduct->overdueDaysForNPA; //6
        $loan_data['submittedOnDate'] = $date;
        $loan_data['repaymentsStartingFromDate'] = $groupMeetingDate;
        $loan_data['calendarId'] = $calendarId;
        $loan_data['linkAccountId'] = $linkAccountId;
        $dData = array();
        $dData['expectedDisbursementDate'] = $disbursement_date;
        $dData['principal'] = $amount;
        $dData['approvedPrincipal'] = $amount;
        $loan_data['disbursementData'] = array();

        $postURl = $config->mifos_url . "fineract-provider/api/v1/loans?tenantIdentifier=" .$config->tenant;
        // post the encoded application details
        $loanApplication = self::MifosPostTransaction($postURl, json_encode($loan_data),$config);

        return $loanApplication;
    }

    public static function getUserGroupId($clientId,$config)
    {
        // get the user's details
        $url = $config->mifos_url . "fineract-provider/api/v1/clients/" . $clientId . "?tenantIdentifier=" .$config->tenant;

        // get the details from Mifos
        $user = self::MifosGetTransaction($url, $post_data = "",$config);

        // get the group of the user
        $groups = $user->groups;
//
//        print_r($groups);
//        exit;

        return $groups[0]->id;
    }

    public static function getUserGroup($groupId,$config)
    {
        // get the user's details
        $url = $config->mifos_url . "fineract-provider/api/v1/groups/" . $groupId . "?associations=all&tenantIdentifier=" .$config->tenant;

        // get the details from Mifos
        $group = self::MifosGetTransaction($url, $post_data = "",$config);

        return $group;
    }

    public static function getClientLoanAccounts($client_id,$config)
    {

        $url = $config->mifos_url . "fineract-provider/api/v1/clients/" . $client_id . "/accounts?fields=loanAccounts&tenantIdentifier=" .$config->tenant;
        $loanAccounts = self::MifosGetTransaction($url,null,$config);
        if (!empty($loanAccounts->loanAccounts)) {
            $loanAccounts = array_reverse($loanAccounts->loanAccounts);
        } else {
            $loanAccounts = array();
        }
        return $loanAccounts;
    }

    public static function getClientSavingsAccounts($client_id,$config)
    {
        $url = $config->mifos_url . "fineract-provider/api/v1/clients/" . $client_id . "/accounts?fields=savingsAccounts&tenantIdentifier=" .$config->tenant;
        $savingsAccounts = self::MifosGetTransaction($url,null,$config);
        if (!empty($savingsAccounts->savingsAccounts)) {
            $savingsAccounts = array_reverse($savingsAccounts->savingsAccounts);
        } else {
            $savingsAccounts = array();
        }
        return $savingsAccounts;
    }

}
