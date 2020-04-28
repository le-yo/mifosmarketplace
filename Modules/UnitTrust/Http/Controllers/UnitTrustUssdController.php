<?php

namespace Modules\UnitTrust\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\MifosUssd\Entities\MifosUssdConfig;
use Modules\MifosUssd\Entities\MifosUssdSession;
use Modules\MifosHelper\Http\Controllers\MifosHelperController;
use Modules\MifosUssd\Entities\MifosUssdMenu;
use Modules\MifosUssd\Entities\MifosUssdSetting;
use Modules\MifosUssd\Entities\MifosUssdUserMenuSkipLogic;

class UnitTrustUssdController extends Controller
{
    public function app(Request $request,$app)
    {
        error_reporting(0);
        header('Content-type: text/plain');
        set_time_limit(100000);

        //check if the app exists

        $app = MifosUssdConfig::whereAppName($app)->first();

        if(!$app){
            $mifos_setting = MifosUssdSession::whereSlug('no_app_found')->first();
            $response = $mifos_setting->value;
            UnitTrustUssdHelperController::sendResponse($response,3,null,null,null);
        }

        //get inputs
        $input = UnitTrustUssdHelperController::getInputs($request,$app);

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
        if (UnitTrustUssdHelperController::user_is_starting($input->latest_text)) {
            $mifos_ussd_session = UnitTrustUssdHelperController::resetUser($mifos_ussd_session,$app);
            $root_menu = MifosUssdMenu::whereAppIdAndIsRoot($app->id,1)->first();
            $response = UnitTrustUssdHelperController::nextMenuSwitch($mifos_ussd_session,$root_menu);
            UnitTrustUssdHelperController::sendResponse($response, 1, $mifos_ussd_session,$app,$input);
        } else {
            $message = $input->latest_text;
//            print_r($mifos_ussd_session);
//            exit;
            switch ($mifos_ussd_session->session) {

                case 0 :
                    //neutral user
                    break;
                case 1 :
                    //user authentication
                    break;
                case 2 :
                    $response = UnitTrustUssdHelperController::continueUssdProgress($mifos_ussd_session, $message);
                    //echo "Main Menu";
                    break;
                case 3 :
                    //confirm USSD Process
                    $response = UnitTrustUssdHelperController::confirmUssdProcess($mifos_ussd_session, $message);
                    break;
                case 4 :
                    //Go back menu
                    $response = UnitTrustUssdHelperController::confirmGoBack($mifos_ussd_session, $message);
                    break;
                case 5 :
                    //Go back menu
                    $response = self::resetPIN($mifos_ussd_session, $message);
                    break;
                case 6 :

                    //accept terms and conditions
                    $menu = MifosUssdMenu::find($mifos_ussd_session->menu_id);
                    $response = UnitTrustUssdHelperController::customApp($mifos_ussd_session, $menu,$message);
                    break;
                default:
                    break;
            }
            UnitTrustUssdHelperController::sendResponse($response, 1, $mifos_ussd_session,$app,$input);
        }


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

}
