<?php

namespace Modules\MifosReminder\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Modules\MifosHelper\Http\Controllers\MifosHelperController;
use Modules\MifosReminder\Entities\MifosReminderConfig;
use Modules\MifosReminder\Entities\MifosReminderOutbox;

class SendReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reminder;

    /**
     * Create a new job instance.
     * @param $reminder
     */
    public function __construct($reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', '-1');

        $config = MifosReminderConfig::find($this->reminder->mifos_reminder_app_id);
        $response = MifosHelperController::listAllDueAndOverdueClients($config,$this->reminder);

        foreach ($response as $sd)
        {

            $check = MifosReminderOutbox::wherePhone('254'.substr($sd['Mobile No'], -9))->whereDate('created_at', Carbon::today())->whereReminderId($this->reminder->id)->first();

            if($check) {
                continue;
            }

            // Retrieve the loan details
            $loanDataResponse = MifosHelperController::getLoan(substr($sd['Loan Account Number'], -9),$config);
            $url = $config->mifos_url . "fineract-provider/api/v1/loanproducts/" . $loanDataResponse->loanProductId . "?" . $config->tenant;
            $loanproduct = MifosHelperController::get($url,$config);
            // Get the url for retrieving the specific loan
            $no = substr($sd['Mobile No'], -9);

            $url = $config->mifos_url . "fineract-provider/api/v1/clients?sqlSearch=(c.mobile_no%20like%20%27" . $no . "%27)&tenantIdentifier=" . $config->tenant;

            $client = MifosHelperController::get($url,$config);

            if ($client->totalFilteredRecords == 0) {
                $url = $config->mifos_url . "fineract-provider/api/v1/clients?sqlSearch=(c.mobile_no%20like%20%270" . $no . "%27)&tenantIdentifier=" . $config->tenant;
                $client = MifosHelperController::get($url,$config);

                if ($client->totalFilteredRecords == 0) {
                    $url = $config->mifos_url . "fineract-provider/api/v1/clients?sqlSearch=(c.mobile_no%20like%20%27254" . $no . "%27)&tenantIdentifier=" . $config->tenant;
                    $client = MifosHelperController::get($url,$config);
                }
            }
            $sd['loanproduct'] = $loanproduct;
            $sd['client'] = $client;
            // Get the loan schedule periods
            $schedule = $loanDataResponse->repaymentSchedule;
            $periods = $schedule->periods;
            array_splice($periods, 0, 1);

            // Get all unpaid periods
            $period = array_values(Arr::where($periods, function ($value, $key) use ($sd) {
                return $value->complete == false && Carbon::parse(implode('-', $value->dueDate))->eq(new Carbon($sd['Due Date']));
            }));

            if (count($period) !== 0)
            {

                // Set the correct balance for the period
                $sd['Loan Balance'] = $period[0]->totalOutstandingForPeriod;
                $sd['totalOverdue'] = $loanDataResponse->summary->totalOverdue;

                $exploded = explode("-",$sd['Due Date']);
                $due_date = Carbon::createFromDate($exploded[0], $exploded[1], $exploded[2]);
                $diff = Carbon::now()->diffInDays($due_date,false);
//                print_r("diff is ".$diff. "reminder is ".$this->reminder->day);
//                exit;
                if($diff == $this->reminder->day) {
                    self::sendReminder($this->reminder,$sd,$config);
                }
            }
        }

    }

    public function sendReminder($reminder,$sd,$config)
    {

        //populate outbox
        $message = new MifosReminderOutbox();
        $message->app_id = $reminder->mifos_reminder_app_id;
        $message->phone = '254'.substr($sd['Mobile No'], -9);
        $message->status = 0;
        $message->reminder_id = $reminder->id;
        $message->content = json_encode($sd);
        $search  = array('{prefix}','{external_id}','{phone}', '{due_date}', '{amount}', '{name}', '{principal}', '{interest}', '{penalties}', '{instalment}','{totalOverdue}');
        $replace = array($sd['loanproduct']->shortName,$sd['client']->pageItems[0]->externalId,$sd['Mobile No'], $sd['Due Date'], number_format($sd['Loan Balance'],2), $sd['Client Name'],number_format($sd['Principal Due'],2),number_format($sd['Interest Due'],2),number_format($sd['Penalty Due'],2),number_format($sd['Total Due'],2),number_format($sd['totalOverdue'],2));
        $subject = $reminder->message;
        $msg = str_replace($search, $replace, $subject);
        $message->message = $msg;
        $message->save();
        if(strlen($sd['Mobile No']) > 7) { 

//            $response = MifosHelperController::sendSms('254728355429',$msg,$config);
            if($config->mifos_reminder_sms_gateway_id == 2){
                $response = MifosHelperController::sendSmsViaAT('254'.substr($sd['Mobile No'], -9),$msg,$config);
                if($response[0]->statusCode == 101){
                    $message->status= 1;
                    $message->cost = $response[0]->cost;
                    $message->messageId= $response[0]->messageId;
                    $message->messageParts= $response[0]->messageParts;
                    $message->save();
                }
            }
            if($config->mifos_reminder_sms_gateway_id == 1){
                $response = MifosHelperController::sendSmsViaWasiliana('254'.substr($sd['Mobile No'], -9),$msg,$config);
                if($response->status == 'success'){
                    $message->status= 1;
                    $message->cost = 0;
                    $message->messageId= "";
                    $message->messageParts= "";
                    $message->save();
                }
            }
        }

    }
}
