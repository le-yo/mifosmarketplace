<?php

namespace Modules\MifosSms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\MifosHelper\Http\Controllers\MifosHelperController;
use Modules\MifosInstance\Entities\MifosInstanceConfig;
use Modules\MifosSms\Entities\HookLog;
use Modules\MifosSms\Entities\HookNotification;
use Modules\MifosSms\Entities\MifosSmsConfig;

class HookController extends Controller
{

    public function setHook(){
        //endpoint https://hazinatrust.mifosconnect.com/fineract-provider/api/v1/hooks
        /*{"displayName":"Loan Rejected","isActive":true,"name":"Web","config":{"Content Type":"json","Payload URL":"http://fd81d54d.ngrok.io/loan/rejected?"},"events":[{"entityName":"LOAN","actionName":"REJECT"}]}*/
        $config = MifosSmsConfig::whereAppId(3)->first();

    }

    public function hookNotification(Request $request,$app)
    {
        $request->headers->set('content-type', 'application/json');
        $details = $request->all();
        /**
         * Log the hook data
         */
        $hooklog = new HookLog();
        $hooklog->details = json_encode($details);
        $hooklog->save();
        $entity = $details['changes']['status']['code'];
        $action = $details['changes']['status']['value'];
        $notification = HookNotification::whereAppIdAndEntityNameAndActionName(3,$entity,$action)->first();

        $config = MifosInstanceConfig::whereSlug($app)->first();

        if($notification->is_active == 1) {
            switch (trim($entity)) {
                case 'loanStatusType.approved':
                    //Loan Hook Notifications actions
                    $smsConfig = MifosSmsConfig::whereAppId($config->id)->first();
                    $client = MifosHelperController::getClientByClientId($details['clientId'],$config);
                    $search  = array('{first_name}','{account_id}');
                    $replace = array($client->firstname,$details['loanId']);
                    $msg = str_replace($search, $replace, $notification->message);
                    //send SMS
                    return MifosSmsController::sendSms($client->mobileNo,$msg,$smsConfig);
                    break;
                case 'loanStatusType.rejected':
                    //Loan Hook Notifications actions
                    $smsConfig = MifosSmsConfig::whereAppId($config->id)->first();
                    $client = MifosHelperController::getClientByClientId($details['clientId'],$config);
                    $search  = array('{first_name}','{account_id}');
                    $replace = array($client->firstname,$details['loanId']);
                    $msg = str_replace($search, $replace, $notification->message);
                    //send SMS
                    return MifosSmsController::sendSms($client->mobileNo,$msg,$smsConfig);
                    break;
                default :
                    break;
            }
        }
    }

//    public function loanHookActions($notification,$action,$request){
//
////        switch (strtoupper(trim($action))) {
////            case 'APPROVE':
////                //Loan Hook Notifications actions
////                self::loanApproved($notification, $action, $request);
////                break;
////            case 'REJECT':
////                //Loan Hook Notifications actions
////                self::loanRejected($notification, $action, $request);
////                break;
////            default :
////                break;
////        }
//    }
//    public function loanApproved($notification,$action,$request){
//        //check if the config is active
//            //get the loan details
//                $app = MifosSmsConfig::whereAppId($notification->app_id)->first();
//
////            $loan = MifosHelperController::getLoan($request['loan_id'],$app);
//                $client = MifosHelperController::getClientByClientId($request['client_id'],$app);
//                $search  = array('{first_name}','{account_id}');
//                $replace = array($client->firstname,$request['loan_id']);
//                $msg = str_replace($search, $replace, $notification->message);
//                $MifosSmsConfig = MifosSmsConfig::whereAppId(3)->first();
//                //send SMS
//                MifosSmsController::sendSms($client->mobileNo,$msg,$app);
//
//        }
}
