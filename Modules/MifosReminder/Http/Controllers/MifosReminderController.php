<?php

namespace Modules\MifosReminder\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Modules\MifosHelper\Http\Controllers\MifosHelperController;
use Modules\MifosReminder\Entities\MifosReminder;
use Modules\MifosReminder\Entities\MifosReminderConfig;
use Modules\MifosReminder\Entities\MifosReminderOutbox;

class MifosReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    protected $dueReminderCount;
    protected $overdueReminderCount;

    public function __construct()
    {
        $this->dueReminderCount = 0;
        $this->overdueReminderCount = 0;
    }

    public function send()
    {
        $app_id = 1;
        $config = MifosReminderConfig::find($app_id);
        //get configured messages


        $response = MifosHelperController::listAllDueAndOverdueClients($config);

        foreach ($response as $sd)
        {
            $check = MifosReminderOutbox::wherePhone('254'.substr($sd['Mobile No'], -9))->whereDate('created_at', Carbon::today())->first();

            if($check) {
                continue;
            }

            // Retrieve the loan details
            $loanDataResponse = MifosHelperController::getLoan(substr($sd['Loan Account Number'], -9),$config);

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

//                $diff = "-3";
                //check if there is reminder for that day:
                $reminder = MifosReminder::where("day","=",$diff)->first();
                if($reminder) {
                    self::sendReminder($reminder->message,$sd,$config);
                    exit;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'due_reminders_sent' => $this->dueReminderCount,
                'overdue_reminders_sent' => $this->overdueReminderCount
            ],
            'message' => 'Successfully sent reminders'
        ]);
    }

    public function sendReminder($reminder,$sd,$config)
    {

            //populate outbox
            $message = new MifosReminderOutbox();
            $message->app_id = 1;
            $message->phone = '254'.substr($sd['Mobile No'], -9);
            $message->status = 0;
            $message->content = json_encode($sd);
            $search  = array('{phone}', '{due_date}', '{amount}', '{name}', '{principal}', '{interest}', '{penalties}', '{instalment}','{totalOverdue}');
            $replace = array($sd['Mobile No'], $sd['Due Date'], number_format($sd['Loan Balance'],2), $sd['Client Name'],number_format($sd['Principal Due'],2),number_format($sd['Interest Due'],2),number_format($sd['Penalty Due'],2),number_format($sd['Total Due'],2),number_format($sd['totalOverdue'],2));
            $subject = $reminder;
            $msg = str_replace($search, $replace, $subject);
            $message->message = $msg;
            $message->save();

            if(strlen($sd['Mobile No']) > 7) {
                $response = MifosHelperController::sendSms("254728355429","Test Message ".$msg,$config);
                if($response[0]->statusCode == 101){
                    $message->status= 1;
                    $message->cost = $response[0]->cost;
                    $message->messageId= $response[0]->messageId;
                    $message->messageParts= $response[0]->messageParts;
                    $message->save();
                }

            }

    }

    public function index()
    {
        return view('mifosreminder::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('mifosreminder::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('mifosreminder::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('mifosreminder::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
