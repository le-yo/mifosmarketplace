<?php

namespace Modules\MifosHelper\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class MifosHelperController extends Controller
{


    /**
     * Apply for STL loan
     *
     * @param $user
     * @param $amount
     * @return mixed
     */
    public function applyLoan($user, $amount){
        //get loan settings:
        $loan_settings = setting::where('productId', STL_ID)->first();
        if(!$loan_settings){
            $loan_settings = setting::find(1);
        }
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

        $loan_data = array();
        $loan_data['dateFormat'] = 'dd MMMM yyyy';
        $loan_data['locale'] = 'en_GB';
        $loan_data['clientId'] = $user;
        $loan_data['productId'] = $loan_settings->productId;
        $loan_data['principal'] = $amount;
        $loan_data['loanTermFrequency'] = $loan_settings->loanTermFrequency;
        $loan_data['loanTermFrequencyType'] = $loan_settings->loanTermFrequencyType;
        $loan_data['loanType'] = $loan_settings->loanType;
        $loan_data['numberOfRepayments'] = $loan_settings->numberOfRepayments;
        $loan_data['repaymentEvery'] = $loan_settings->repaymentEvery;
        $loan_data['repaymentFrequencyType'] = $loan_settings->repaymentFrequencyType;
        $loan_data['interestRatePerPeriod'] = $loan_settings->interestRatePerPeriod;
        $loan_data['amortizationType'] = $loan_settings->amortizationType;
        $loan_data['interestType'] = 1;
        $loan_data['interestCalculationPeriodType'] = $loan_settings->interestCalculationPeriodType;
        $loan_data['transactionProcessingStrategyId'] = $loan_settings->transactionProcessingStrategyId;
        $loan_data['expectedDisbursementDate'] = $disbursement_date;
        $loan_data['submittedOnDate'] = $date;
        $dData = array();
        $dData['expectedDisbursementDate'] = $disbursement_date;
        $dData['principal'] = $amount;
        $dData['approvedPrincipal'] = $amount;
        $loan_data['disbursementData'] = array();
        array_push($loan_data['disbursementData'],$dData);

        $postURl = MIFOS_URL."/loans?".MIFOS_tenantIdentifier;

        // post the encoded application details
        $loanApplication = Hooks::MifosPostTransaction($postURl, json_encode($loan_data));
        return $loanApplication;
    }

    /**
     * Apply for PCL Loan
     *
     * @param $user
     * @param $amount
     * @param $repaymentPeriods
     * @return mixed
     */
    public function applyPCLLoan($user, $amount, $repaymentPeriods)
    {

        $groupId = self::getUserGroupId($user);
        $user_group = self::getUserGroup($groupId);
        $calendarId = $user_group->collectionMeetingCalendar->id;
        $groupMeetingDate = Carbon::parse(implode('-', $user_group->collectionMeetingCalendar->nextTenRecurringDates[0]))->format('d M Y');

//        print_r($user_group);
//        exit;

        $loan_settings = setting::where('productId', PCL_ID)->first();

        $date = Carbon::now()->format('d M Y');
//        if(Carbon::now()->isWeekend()){
//            if(Carbon::now()->isSaturday()){
//                $disbursement_date = Carbon::now()->addDays(2)->format('d M Y');
//            }else{
//                $disbursement_date = Carbon::now()->addDays(1)->format('d M Y');
//
//            }
//        }else{
        $disbursement_date = Carbon::now()->format('d M Y');
//        }

        //
//        {
//            "locale": "en_GB",
//	"dateFormat": "dd MMMM yyyy",
//	"clientId": "65",
//	"productId": 1,
//	"principal": 10000,
//	"loanTermFrequency": 3,
//	"loanTermFrequencyType": 2,
//	"loanType": "individual",
//	"numberOfRepayments": 3,
//	"repaymentEvery": 1,
//    "repaymentFrequencyType": 2,
//    "interestRatePerPeriod": 7.5,
//    "interestRateFrequencyType": 2,
//    "amortizationType": 1,
//    "interestType": 1,
//    "interestCalculationPeriodType": 1,
//    "transactionProcessingStrategyId": 2,
//    "expectedDisbursementDate": "17 Oct 2017",
//    "submittedOnDate": "17 Oct 2017"
//}
        //
        $interest = self::getGroupInterestRate($user);
        $periods = $repaymentPeriods;

        $loan_data = [];
        $loan_data['locale'] = 'en_GB';
        $loan_data['dateFormat'] = 'dd MMMM yyyy';
        $loan_data['clientId'] = $user;
        $loan_data['productId'] = $loan_settings->productId;
        $loan_data['principal'] = $amount;
        $loan_data['loanTermFrequency'] = $repaymentPeriods;
        $loan_data['loanTermFrequencyType'] = 2; // 1
        $loan_data['loanType'] = 'jlg';
        $loan_data['numberOfRepayments'] = $repaymentPeriods;
        $loan_data['repaymentEvery'] = $loan_settings->repaymentEvery; // 2
        $loan_data['repaymentFrequencyType'] = $loan_settings->repaymentFrequencyType; //3
        $loan_data['interestRatePerPeriod'] = $interest;
        $loan_data['interestRateFrequencyType'] = 2;
        $loan_data['amortizationType'] = $loan_settings->amortizationType; //4
        $loan_data['groupId'] = $groupId;
//        $loan_data['interestType'] = self::getInterestType($loan_settings->productId);
        $loan_data['interestType'] = 1;
        $loan_data['interestCalculationPeriodType'] = $loan_settings->interestCalculationPeriodType; //5
        $loan_data['expectedDisbursementDate'] = $disbursement_date;
//        $loan_data['syncDisbursementWithMeeting'] = 0;
//        $loan_data['syncRepaymentWithMeeting'] = 1;
//        $loan_data['transactionProcessingStrategyId'] = $loan_settings->transactionProcessingStrategyId; //6
        $loan_data['transactionProcessingStrategyId'] = 1; //6
//        $loan_data['repaymentFrequencyNthDayType'] = -1; //This
//        $loan_data['repaymentFrequencyDayOfWeekType'] = 1; //This
        $loan_data['submittedOnDate'] = $date;
        $loan_data['repaymentsStartingFromDate'] = $groupMeetingDate;
        $loan_data['calendarId'] = $calendarId;
        $dData = array();
        $dData['expectedDisbursementDate'] = $disbursement_date;
        $dData['principal'] = $amount;
        $dData['approvedPrincipal'] = $amount;
        $loan_data['disbursementData'] = array();
//        array_push($loan_data['disbursementData'],$dData);

        // url for posting the application details
        $postURl = MIFOS_URL."/loans?".MIFOS_tenantIdentifier;
//        $postURl = MIFOS_URL."/loans/30?".MIFOS_tenantIdentifier;
//        print_r(json_encode($loan_data));
//        exit;
        // post the encoded application details
        $loanApplication = Hooks::MifosPostTransaction($postURl, json_encode($loan_data));
//        $loanApplication = Hooks::MifosGetTransaction($postURl, json_encode($loan_data));
//        print_r($loanApplication);
//        exit;


        return $loanApplication;
    }

    /**
     * Apply for a Asset Financing Loan Product
     * Use is for testing only
     *
     * @param $user
     * @param $amount
     * @param $repaymentPeriods
     * @return mixed
     */
    public function applyASFLoan($user, $amount, $repaymentPeriods)
    {
        $loan_settings = setting::where('productId', ASF_ID)->first();

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

        $interest = self::getGroupInterestRate($user);

        $loan_data = array();
        $loan_data['dateFormat'] = 'dd MMMM yyyy';
        $loan_data['locale'] = 'en_GB';
        $loan_data['clientId'] = intval($user);
        $loan_data['productId'] = $loan_settings->productId;
        $loan_data['principal'] = $amount;
        $loan_data['loanTermFrequency'] = intval($repaymentPeriods);
        $loan_data['loanTermFrequencyType'] = $loan_settings->loanTermFrequencyType;
        $loan_data['loanType'] = $loan_settings->loanType;
        $loan_data['numberOfRepayments'] = intval($repaymentPeriods);
        $loan_data['repaymentEvery'] = $loan_settings->repaymentEvery;
        $loan_data['repaymentFrequencyType'] = $loan_settings->repaymentFrequencyType;
        $loan_data['interestRatePerPeriod'] = $interest;
        $loan_data['amortizationType'] = $loan_settings->amortizationType;
        $loan_data['interestType'] = 0;
        $loan_data['interestCalculationPeriodType'] = $loan_settings->interestCalculationPeriodType;
        $loan_data['transactionProcessingStrategyId'] = $loan_settings->transactionProcessingStrategyId;
        $loan_data['expectedDisbursementDate'] = $disbursement_date;
        $loan_data['submittedOnDate'] = $date;
        $dData = array();
        $dData['expectedDisbursementDate'] = $disbursement_date;
        $dData['principal'] = $amount;
        $dData['approvedPrincipal'] = $amount;
        $loan_data['disbursementData'] = array();
        array_push($loan_data['disbursementData'],$dData);

        // url for posting the application details
        $postURl = MIFOS_URL."/loans?".MIFOS_tenantIdentifier;

        // post the encoded application details
        $loanApplication = Hooks::MifosPostTransaction($postURl, json_encode($loan_data));

        return json_encode($loanApplication);
    }

    /**
     * get group interest rate
     *
     * @param $clientId
     * @return mixed
     */
    public function getGroupInterestRate($clientId)
    {
//        return 7.5;
        // get the group id
        $groupId = self::getUserGroupId($clientId);

        // load the url for getting the group interest
        $url = MIFOS_URL."/datatables/Group%20Interest%20Rate/".$groupId."?".MIFOS_tenantIdentifier;

        // grab the datatable details from Mifos
        $interest = Hooks::MifosGetTransaction($url, $post_data = "");

        if ($interest == [])
        {
            return 7.5;
        }
        else
        {
            return $interest[0]->Rate;
        }
    }

    /**
     * get the group id of the client
     *
     * @param $clientId
     * @return mixed
     */
    public function getUserGroupId($clientId)
    {
        // get the user's details
        $url = MIFOS_URL . "/clients/" . $clientId . "?" . MIFOS_tenantIdentifier;

        // get the details from Mifos
        $user = Hooks::MifosGetTransaction($url, $post_data = "");

        // get the group of the user
        $groups = $user->groups;
//
//        print_r($groups);
//        exit;

        return $groups[0]->id;
    }
    public function getUserGroup($groupId)
    {
        // get the user's details
        $url = MIFOS_URL . "/groups/" . $groupId . "?associations=all&" . MIFOS_tenantIdentifier;

        // get the details from Mifos
        $group = Hooks::MifosGetTransaction($url, $post_data = "");

        return $group;
    }

    /**
     * Gets the interest method for the requested loan
     *
     * @param $productId
     * @return mixed
     */
    public function getInterestType($productId)
    {
        // get the loan product
        $url = MIFOS_URL . "/loanproducts/" . $productId . "?" . MIFOS_tenantIdentifier;

        // get the details from Mifos
        $loanProduct = Hooks::MifosGetTransaction($url, $post_data = "");
        // get the interest type id
        $interestType = $loanProduct->interestType->id;
        return $interestType;
    }

    /**
     * Gets the loan product
     *
     * @param $productId
     * @return mixed
     */
    public function getLoanProduct($productId)
    {
        // get the loan product
        $url = MIFOS_URL . "/loanproducts/" . $productId . "?" . MIFOS_tenantIdentifier;

        // get the details from Mifos
        $loanProduct = Hooks::MifosGetTransaction($url, $post_data = "");

        return $loanProduct;
    }

    /**
     * Gets the Repayment schedule for the requested loan
     *
     * @param $clientId
     * @param $amount
     * @param $loanProductId
     * @param $repaymentPeriods
     * @return mixed
     */

    public function calculateFullRepaymentSchedule($clientId, $amount, $loanProductId, $repaymentPeriods)
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

    public function calculateRepaymentSchedule($clientId, $amount, $loanProductId, $repaymentPeriods)
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

        $loan_data = [];
        $loan_data['dateFormat'] = 'dd MMMM yyyy';
        $loan_data['locale'] = 'en_GB';
        $loan_data['productId'] = $loan_settings->productId;
        $loan_data['clientId'] = $clientId;
        $loan_data['principal'] = $amount;
        $loan_data['loanTermFrequency'] = $periods;
        $loan_data['loanTermFrequencyType'] = $loan_settings->loanTermFrequencyType;
        $loan_data['loanType'] = $loan_settings->loanType;
        $loan_data['numberOfRepayments'] = $periods;
        $loan_data['repaymentEvery'] = $loan_settings->repaymentEvery;
        $loan_data['repaymentFrequencyType'] = $loan_settings->repaymentFrequencyType;
        $loan_data['interestRatePerPeriod'] = $interest;
        $loan_data['amortizationType'] = $loan_settings->amortizationType;
        $loan_data['interestType'] = self::getInterestType($loan_settings->productId);
        $loan_data['interestCalculationPeriodType'] = $loan_settings->interestCalculationPeriodType;
        $loan_data['expectedDisbursementDate'] = $disbursement_date;
        $loan_data['transactionProcessingStrategyId'] = $loan_settings->transactionProcessingStrategyId;
        $loan_data['transactionProcessingStrategyId'] = 2;
        $loan_data['submittedOnDate'] = $date;

        // Get the url for calculating the loan schedule
        $url = MIFOS_URL."/loans?command=calculateLoanSchedule&". MIFOS_tenantIdentifier;

        // Post to the url to receive the schedule as a response
        $loan = Hooks::MifosPostTransaction($url, json_encode($loan_data));

        // Initialize an empty array for the schedule
        $schedule = [];

        // Get the periods for the schedule
        $paymentPeriods = $loan->periods;
//        print_r($paymentPeriods);
//        exit;
        // Loop through all the periods
        for ($i = 0; $i < count($paymentPeriods); $i++)
        {
            // Push only the peroids that have not been paid for
            if (array_key_exists('daysInPeriod', $paymentPeriods[$i])) {
                $outstandingForPeriod = $paymentPeriods[$i]->totalOutstandingForPeriod;
                $paymentDueDate = Carbon::parse($paymentPeriods[$i]->dueDate[0].'-'.$paymentPeriods[$i]->dueDate[1].'-'.$paymentPeriods[$i]->dueDate[2])->format('j F Y');
                array_push($schedule, $outstandingForPeriod);
            }
        }
        return $schedule[0];
    }

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


    public static function listAllDueAndOverdueClients($mifos_url,$username,$pass,$tenant)
    {
        // Get the url for running the report
//        $getURl = $mifos_url."fineract-provider/api/v1/runreports/Loan%20Payments%20Due%20Report?";
        $getURl = $mifos_url."fineract-provider/api/v1/runreports/Loan%20Payments%20Due%20Report?R_startDate=".Carbon::today()->subDays(16)->format('Y-m-d')."&R_endDate=".Carbon::today()->addDays(6)->format('Y-m-d')."&R_officeId=1&R_currencyId=-1&R_loanProductId=1";

        // Send a GET request
        $reports = self::get($getURl,$username,$pass,$tenant);

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


    public static function get($url,$username,$pass,$tenant)
    {

        $data = ['slug' => 'mifos_get_request', 'content' => $url];
        //log request
//        Log::create($data);
        $client = new Client(['verify' => false]);
        $credentials = base64_encode($username.':'.$pass);

        try {
            $data = $client->get($url,
                [
                    'headers' => [
                        'Authorization' => 'Basic '.$credentials,
                        'Content-Type' => 'application/json',
                        'Fineract-Platform-TenantId' => $tenant
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

}
