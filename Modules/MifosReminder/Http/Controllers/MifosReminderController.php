<?php

namespace Modules\MifosReminder\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Modules\MifosHelper\Http\Controllers\MifosHelperController;
use Modules\MifosReminder\Entities\MifosReminder;
use Modules\MifosReminder\Entities\MifosReminderConfig;
use Modules\MifosReminder\Entities\MifosReminderOutbox;
use Modules\MifosReminder\Jobs\SendReminder;

class MifosReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */

    public function __construct()
    {

    }


    public function send()
    {
        //get configured messages
        $reminders = MifosReminder::where("schedule_time","=",Carbon::now()->format('H:i'))->get();

        if($reminders){
            foreach ($reminders as $key=>$reminder){
                $job = (new SendReminder($reminder))->onQueue('reminders');
                $this->dispatch($job);
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
