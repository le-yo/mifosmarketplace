<?php

namespace Modules\MifosReminder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

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
        $mifos = new MifosXController();
        $response = $mifos->listAllDueAndOverdueClients();

        foreach ($response as $sd)
        {
            $check = Outbox::wherePhone('254'.substr($sd['Mobile No'], -9))->whereIsReminder(1)->whereDate('created_at', Carbon::today())->first();

            if($check) {
                continue;
            }

            // Retrieve the loan details
            $loanData = new HooksController();
            $loanDataResponse = $loanData->getLoan(substr($sd['Loan Account Number'], -9));

            // Get the loan schedule periods
            $schedule = $loanDataResponse->repaymentSchedule;
            $periods = $schedule->periods;
            array_splice($periods, 0, 1);

            // Get all unpaid periods
            $period = array_values(array_where($periods, function ($value, $key) use ($sd) {
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

                if($diff<0) {
                    $reminder = Reminder::whereDaysOverdue(abs($diff))->first();
                    self::sendReminder($reminder,$sd, 1);
                } else {
                    $reminder = Reminder::whereDaysTo($diff)->first();
                    self::sendReminder($reminder,$sd, 0);
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

    public function sendReminder($reminder,$sd, $type)
    {
        if($reminder) {
            $notify = new NotifyController();

            //populate outbox
            $message = new Outbox();
            $message->phone = '254'.substr($sd['Mobile No'], -9);
            $message->reminder_id = $reminder->id;
            $message->status = 0;
            $message->is_reminder = 1;
            $message->content = json_encode($sd);
            $search  = array('{phone}', '{due_date}', '{balance}', '{name}', '{principal}', '{interest}', '{penalties}', '{instalment}','{totalOverdue}');
            $replace = array($sd['Mobile No'], $sd['Due Date'], number_format($sd['Loan Balance'],2), $sd['Client Name'],number_format($sd['Principal Due'],2),number_format($sd['Interest Due'],2),number_format($sd['Penalty Due'],2),number_format($sd['Total Due'],2),number_format($sd['totalOverdue'],2));
            $subject = $reminder->message;
            $msg = str_replace($search, $replace, $subject);
            $message->message = $msg;
            $message->save();

            if(strlen($sd['Mobile No']) > 7) {
                $notify->sendSms($sd['Mobile No'],$msg);
                $message->status= 1;
                $message->save();
            }

            if ($type == 1)
            {
                $this->overdueReminderCount++;
            } else {
                $this->dueReminderCount++;
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
