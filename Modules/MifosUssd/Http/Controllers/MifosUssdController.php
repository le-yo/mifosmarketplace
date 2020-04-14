<?php

namespace Modules\MifosUssd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\MifosHelper\Http\Controllers\MifosHelperController;
use Modules\MifosUssd\Entities\MifosUssdConfig;
use Modules\MifosUssd\Entities\MifosUssdMenu;
use Modules\MifosUssd\Entities\MifosUssdMenuItems;
use Modules\MifosUssd\Entities\MifosUssdSession;
use Modules\MifosUssd\Entities\MifosUssdSetting;
use Modules\MifosUssd\Entities\MifosUssdUserMenuSkipLogic;

class MifosUssdController extends Controller
{

    public function idfix(){

        $app = MifosUssdConfig::whereAppName('hazina')->first();


        $skips = MifosUssdUserMenuSkipLogic::all();
        foreach ($skips as $skip){
            //get client by phone
            //get the current user
            $session = MifosUssdSession::wherePhone($skip->phone)->first();
            $client = MifosHelperController::getClientUsingPhone($skip->phone,$app);

            $client_details = json_decode($session->other);

            if($client_details->client_id == $client->id){
                echo $session->phone." iko sawa".PHP_EOL;
            }else{
                //check if the wrong account has a loan pending approval:
                $loan = self::checkLoanPendingApproval($client_details->client_id,$app,$client->id,$skip);
                if(isset($loan->loanId)){
                    echo $session->phone." wrong Id:".$client_details->client_id." Correct ID :".$client->id."Correct Loan ".$loan->loanId.PHP_EOL;
                    //update the
                    $client_details->client_id = $client->id;
                    $session->other = json_encode($client_details);
                }
                //PHP_EOL;
            }
//            exit;

            //confirm client_id
        }

        exit;
    }
    public function checkLoanPendingApproval($clientId,$config,$correctid,$skip){
        $loanAccounts = MifosHelperController::getClientLoanAccounts($clientId,$config);
        if($loanAccounts){
                if($loanAccounts[0]->status->code =='loanStatusType.submitted.and.pending.approval'){
                    //getloanaccount
                    $loan = MifosHelperController::getLoanDetails($loanAccounts[0]->id,$config);
                    //apply for the loan
                    if($skip->skip==1){
                        $response = MifosHelperController::applyLoan($loan->loanProductId,$correctid,$loan->principal,$config);
                        if (!empty($response->loanId)) {
                            $skips = MifosUssdUserMenuSkipLogic::wherePhone($skip->phone)->first();
                            $skips->skip=2;
                            $skips->save();
                        }
                    }else{
                        $loanAccounts = MifosHelperController::getClientLoanAccounts($correctid,$config);
                        $loan = array();
                        $loan['loanId'] = $loanAccounts[0]->id;
                        $loan = (object) $loan;
                        $response = $loan;
                    }

                    return $response;
                }
        }else{
            return $loanAccounts;
        }

    }
    public function app(Request $request,$app)
    {
        error_reporting(0);
        header('Content-type: text/plain');
        set_time_limit(100000);

        //check if the app exists

        $app = MifosUssdConfig::whereAppName($app)->first();

        if(!$app){
            $mifos_setting = MifosUssdSetting::whereSlug('no_app_found')->first();
            $response = $mifos_setting->value;
            MifosUssdHelperController::sendResponse($response,3,null,null,null);
        }

        //get inputs
        $input = MifosUssdHelperController::getInputs($request,$app);

        $mifos_ussd_session = MifosUssdSession::wherePhone($input->phone)->first();
        //get the session
        if(!$mifos_ussd_session){
            $mifos_ussd_session = new MifosUssdSession();
            $mifos_ussd_session->phone = $input->phone;
            $mifos_ussd_session->save();
        }else{
            $mifos_ussd_session->app_id = $app->id;
            $mifos_ussd_session->save();
        }


        //check if the user/phone is starting
        if (MifosUssdHelperController::user_is_starting($input->latest_text)) {

            $mifos_ussd_session = MifosUssdHelperController::resetUser($mifos_ussd_session,$app);
            $root_menu = MifosUssdMenu::whereAppIdAndIsRoot($app->id,1)->first();
            $response = MifosUssdHelperController::nextMenuSwitch($mifos_ussd_session,$root_menu);
            MifosUssdHelperController::sendResponse($response, 1, $mifos_ussd_session,$app,$input);
        } else {
            $message = $input->latest_text;

            switch ($mifos_ussd_session->session) {

                case 0 :
                    //neutral user
                    break;
                case 1 :
                    //user authentication
                    break;
                case 2 :
                    $response = MifosUssdHelperController::continueUssdProgress($mifos_ussd_session, $message);
                    //echo "Main Menu";
                    break;
                case 3 :
                    //confirm USSD Process
                    $response = self::confirmUssdProcess($mifos_ussd_session, $message);
                    break;
                case 4 :
                    //Go back menu
                    $response = MifosUssdHelperController::confirmGoBack($mifos_ussd_session, $message);
                    break;
                case 5 :
                    //Go back menu
                    $response = self::resetPIN($mifos_ussd_session, $message);
                    break;
                case 6 :

                    //accept terms and conditions
                    $menu = MifosUssdMenu::find($mifos_ussd_session->menu_id);
                    $response = MifosUssdHelperController::customApp($mifos_ussd_session, $menu,$message);
                    break;
                default:
                    break;
            }
            MifosUssdHelperController::sendResponse($response, 1, $mifos_ussd_session,$app,$input);
        }


    }


    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('mifosussd::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('mifosussd::create');
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
        return view('mifosussd::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('mifosussd::edit');
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
