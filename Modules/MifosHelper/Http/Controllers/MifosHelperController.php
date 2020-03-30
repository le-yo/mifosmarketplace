<?php

namespace Modules\MifosHelper\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Modules\MifosReminder\Entities\MifosReminderOutbox;

class MifosHelperController extends Controller
{

    public function checkNextInstallment($loanId)
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
//        Log::create($data);
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
//        Log::create($dt);
        curl_close($ch);
        $response = json_decode($data);
        return $response;
    }

    public static function MifosPostTransaction($url,$post_data,$config){

        $data = ['slug' => 'mifos_post_request', 'content' => $post_data];
        //log request
//        Log::create($data);
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
//        Log::create($dt);

        $response = json_decode($data);

        return $response;
    }

}
