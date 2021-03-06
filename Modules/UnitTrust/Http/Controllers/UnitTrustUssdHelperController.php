<?php

namespace Modules\UnitTrust\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Modules\HelaplusTokens\Http\Controllers\HelaplusTokensController;
use Modules\MifosHelper\Http\Controllers\MifosHelperController;
use Modules\MifosSms\Entities\MifosSmsConfig;
use Modules\MifosSms\Http\Controllers\MifosSmsController;
use Modules\MifosUssd\Entities\MifosUssdConfig;
use Modules\MifosUssd\Entities\MifosUssdLog;
use Modules\MifosUssd\Entities\MifosUssdMenu;
use Modules\MifosUssd\Entities\MifosUssdMenuItems;
use Modules\MifosUssd\Entities\MifosUssdResponse;
use Modules\MifosUssd\Entities\MifosUssdSession;
use Illuminate\Support\Facades\Validator;
use Modules\MifosUssd\Entities\MifosUssdUserMenuSkipLogic;
use SmoDav\Mpesa\Laravel\Facades\STK;
use Carbon\Carbon;

class UnitTrustUssdHelperController extends Controller
{
    public static function getInputs($request,$app){


        $input = array();
        $latest_text = '';
        if($app->ussd_gateway_id == 1){
            $input['session_id'] = $request->input('sessionId');
            $input['service_code'] = $request->input('serviceCode');
            $input['phone'] = $request->input('phoneNumber');
            $input['text'] = $request->input('text');   //

            $text_parts = explode("*", $input['text']);

            if (empty($text_parts)) {
                $latest_text = $text_parts;
            } else {
                end($text_parts);
                // move the internal pointer to the end of the array
                $latest_text = current($text_parts);
            }
        }else{
            $input['session_id'] = $request->input('sessionId');
            $input['service_code'] = $request->input('serviceCode');
            $input['phone'] = $request->input('phoneNumber');
            $input['text'] = $request->input('text');   //
        }
        $input['latest_text'] = $latest_text;
        $input = (object) $input;
        $session = MifosUssdSession::wherePhone($input->phone)->first();
        if(!$session){
            $session = new MifosUssdSession();
            $session->phone = $input->phone;
            $session->app_id =0;
            $session->save();
        }
        self::ussdLog($session,$input,0,$input->text);

        return $input;
    }

    public static function continueUssdProgress($session, $message)
    {
        $response = '';
        $menu = MifosUssdMenu::find($session->menu_id);
        //check the user menu

        switch ($menu->type) {
            case 0:
                //authentication mini app

                break;
            case 1:
                //continue to another menu

                $response = self::continueUssdMenu($session, $message, $menu);

                break;
            case 2:

                //continue to a processs
                $response = self::continueSingleProcess($session, $message, $menu);
                break;
            case 3:
                //continue to a processs
                $response = self::continueSingleProcess($session, $message, $menu);
                break;
            case 4:

                $response = self::customApp($session, $menu, $message);

                break;
            default :
                self::resetUser($session);
                $response = "An authentication error occurred";
                break;
        }

        return $response;

    }

    public static function confirmLoanApplication($session,$message,$menuItem){

//            $amount = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,$menuItem->id)->orderBy('id', 'DESC')->first();
//
//
//            $response =
//
//            $MifosX = new MifosXController();
//            $monthly_payment = $MifosX->calculateFullRepaymentSchedule($user->client_id, $amount, PCL_ID, $response->response);
//
//            if ($response->response < 2) {
//
//                $confirmation = $confirmation . PHP_EOL . "Period: " . $response->response . " months";
//                $confirmation = $confirmation . PHP_EOL . "Expected payment: " . $monthly_payment;
//
//            } else {
//                $confirmation = $confirmation . PHP_EOL . "Period: " . $response->response . " month";
//                $confirmation = $confirmation . PHP_EOL . "REPAYMENT(S) : " . PHP_EOL . $monthly_payment;
//            }
//
//        $response = $confirmation . PHP_EOL . "1. Yes" . PHP_EOL . "2. No";
//
//        print_r($session);
//        exit;
    }

    public static function continueSingleProcess($session, $message, $menu)
    {
        self::storeUssdResponse($session, $message);

        //validate input to be numeric
        $menuItem = MifosUssdMenuItems::whereMenuIdAndStep($menu->id, $session->progress)->first();

        if($menuItem->validation == 'custom'){
            if(self::customValidation($session,$message,$menuItem)){
                $step = $session->progress + 1;
            }
        }elseif($menuItem->validation == 'schedule'){
            if($session->confirm_from == 0){
                $response = "Confirm ".$menu->title.PHP_EOL."Amount ".$message;
                $response = $response . PHP_EOL . "1. Yes" . PHP_EOL . "2. No";
                $session->confirm_from = $menuItem->id;
                $session->save();
                return $response;
            }else{
                $amount = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,$menuItem->id)->where('response', '!=' , 1)->orderBy('id', 'DESC')->first();

                $amount = $amount->response;

                $other = json_decode($session->other);

                $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                if($menuItem->id == 28){
                    $product_id =7;
                }else{
                    $product_id = 2;
                }
                //apply for the loan
                $response = MifosHelperController::applyLoan($product_id,$other->client_id,$amount,$config);

                if (empty($response->loanId)) {
                    $response = "We had a problem processing your loan. Kindly retry or contact customer care";
                    $message = "Dear {first_name}, your loan request of {amount} was not successfully submitted. Please try again or call us on 0706247815 / 0784247815 for assistance.";

                    $client = MifosHelperController::getClientByClientId($other->client_id,$config);
                    $search  = array('{first_name}','{amount}');
                    $replace = array($client->firstname,$amount);
                    $msg = str_replace($search, $replace, $message);
                    $MifosSmsConfig = MifosSmsConfig::whereAppId(3)->first();
                    MifosSmsController::sendSMSViaConnectBind($session->phone,$msg,$MifosSmsConfig);
                    //self::resetUser($user);
                    self::sendResponse($response, 2, $session);
                } else {
                    $ussd_message = "You loan application has been received successfully";
                    $message = "Dear {first_name}, your loan request of {amount} has been received and is undergoing approval as loan {loan_account_number}. Please wait for confirmation.";
                    $client = MifosHelperController::getClientByClientId($response->clientId,$config);
                    $search  = array('{first_name}','{amount}','{loan_account_number}');
                    $replace = array($client->firstname,$amount,$response->loanId);
                    $msg = str_replace($search, $replace, $message);
                    $MifosSmsConfig = MifosSmsConfig::whereAppId(3)->first();
                    MifosSmsController::sendSMSViaConnectBind($session->phone,$msg,$MifosSmsConfig);
                    self::sendResponse($ussd_message,2,$session);
                }
            }
        }else{

            $validator = Validator::make(array('field'=>$message), [
                'field' => $menuItem->validation,
            ]);
            if($validator->fails()){
                //validation failed
                $response = 'Invalid input';
            }else{
                //validation is fine
                $step = $session->progress + 1;
            }
        }

        $menuItem = MifosUssdMenuItems::whereMenuIdAndStep($menu->id, $step)->first();
        if ($menuItem) {
            $session->menu_item_id = $menuItem->id;
            $session->menu_id = $menu->id;
            $session->progress = $step;
            $session->save();
            return $response. $menuItem->description;
        } else {
            if($menu->id == 1){
                $response = $menu->confirmation_message;
                $skipLogic = MifosUssdUserMenuSkipLogic::wherePhoneAndMifosUssdMenuId($session->phone,$menu->id)->first();
                if(!$skipLogic){
                    $skipLogic = new MifosUssdUserMenuSkipLogic();
                }
                $skipLogic->phone = $session->phone;
                $skipLogic->mifos_ussd_menu_id = $menu->id;
                $skipLogic->skip = true;
                $skipLogic->save();
                //send SMS
                $MifosSmsConfig = MifosSmsConfig::whereAppId(3)->first();
                MifosSmsController::sendSMSViaConnectBind($session->phone,$response,$MifosSmsConfig);
                self::sendResponse($response,3,$session);
            }else{
                $response = self::confirmBatch($session, $menu);
            }
            return $response;
        }
    }

    public static function customValidation($session,$message,$menuItem){

        switch ($menuItem->id) {
            case 1:
                $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                //validate national ID from Mifos
                $response = MifosHelperController::getClientByNationalId($message,$config);
                if(isset($response[0])){
                    if($response[0]->entityType == 'CLIENTIDENTIFIER'){
                        //check if ID belongs to the same client
                        $client = MifosHelperController::getClientbyClientId($response[0]->parentId,$config);
                        if(substr($client->mobileNo,-9) == (substr($session->phone,-9))){
                            $client_details = array('client_id'=>$response[0]->parentId,'external_id'=>$message);
                            $session->other = json_encode($client_details);
                            $session->save();
                            return TRUE;
                        }else{
                            $response = "National ID is valid but belongs to a different phone number.".PHP_EOL."Please enter your ID";
                            self::sendResponse($response,1,$session);
                        }
                    }else{
                        return FALSE;
                    }
                }else{
                    $response = "National ID is not registered. Service only available to registered customers";
                    self::sendResponse($response,1,$session);
                }
                break;
            case 3:
                //veify if the PINs are equal
                $PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,2)->orderBy('id', 'DESC')->first();
                $CONFIRM_PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,2)->orderBy('id', 'DESC')->first();
                if($PIN->response == $CONFIRM_PIN->response){
                    //set PIN and send to Mifos
                    $datatable = array(
                        "PIN" => Crypt::encrypt($PIN->response),
                        "locale"=>"en",
                        "dateFormat"=> "dd MMMM yyyy"
                    );
                    $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                    $client_details = json_decode($session->other);
                    $r = MifosHelperController::setDatatable('PIN',$client_details->client_id,json_encode($datatable),$config);

                    if (!empty($r->errors)) {

                        if (strpos($r->defaultUserMessage, 'already exists')) {
                            //we try to update
                            $r = MifosHelperController::updateDatatable('PIN',$client_details->client_id,json_encode($datatable),$config,1);
                        }
                        if(!empty($r->errors)){
                            $error_msg = 'We had a problem setting your PIN. Kindly retry or contact Customer Care';
                            self::sendResponse($error_msg,1,$session);
                        }
                    }
                    // post the encoded application details
//                    $r = MifosHelperController::MifosPostTransaction($postURl, json_encode($datatable),$config);

                    //store PIN in session
                    $client_details->pin = Crypt::encrypt($PIN->response);
                    $session->other = json_encode($client_details);
                    $session->save();

                    return TRUE;
                }else{
                    $step = $session->progress - 1;
                    $session->progress = $step;
                    $session->save();
                    return FALSE;
                }
                break;
            case 4:
                if($message == '0'){
                    $menu = MifosUssdMenu::find(12);

                    $response = UnitTrustUssdHelperController::nextMenuSwitch($session,$menu);
                    UnitTrustUssdHelperController::sendResponse($response, 1, $session,null);
                }else{
                    $response = self::validatePIN($session,$message);
                }
                break;
            case 5:
                $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                //validate national ID from Mifos
                $response = MifosHelperController::getClientByNationalId($message,$config);
                if(isset($response[0])){
                    if($response[0]->entityType == 'CLIENTIDENTIFIER'){
                        //check if ID belongs to the same client
                        $client = MifosHelperController::getClientbyClientId($response[0]->parentId,$config);
                        if(substr($client->mobileNo,-9) == (substr($session->phone,-9))){
                            $client_details = array('client_id'=>$response[0]->parentId,'external_id'=>$message);
                            $session->other = json_encode($client_details);
                            return TRUE;
                        }else{
                            $response = "National ID is valid but belongs to a different phone number.".PHP_EOL."Please enter your ID";
                            self::sendResponse($response,1,$session);
                        }
                    }else{
                        return FALSE;
                    }
                }
                break;
            case 6:
                //veify if the IDs are equal
                $PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,2)->orderBy('id', 'DESC')->first();
                $CONFIRM_PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,2)->orderBy('id', 'DESC')->first();
                if($PIN->response == $CONFIRM_PIN->response){
                    //set PIN and send to Mifos
                    $datatable = array(
                        "PIN" => Crypt::encrypt($PIN->response),
                        "locale"=>"en",
                        "dateFormat"=> "dd MMMM yyyy"
                    );
                    $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                    $client_details = json_decode($session->other);
                    $r = MifosHelperController::setDatatable('PIN',$client_details->client_id,json_encode($datatable),$config);

                    if (!empty($r->errors)) {

                        if (strpos($r->defaultUserMessage, 'already exists')) {
                            //we try to update
                            $r = MifosHelperController::updateDatatable('PIN',$client_details->client_id,json_encode($datatable),$config);
                        }
                        if(!empty($r->errors)){
                            $error_msg = 'We had a problem setting your PIN. Kindly retry or contact Customer Care';
                            self::sendResponse($error_msg,1,$session);
                        }
                    }
                    // post the encoded application details
//                    $r = MifosHelperController::MifosPostTransaction($postURl, json_encode($datatable),$config);

                    //store PIN in session
                    $client_details->pin = Crypt::encrypt($PIN->response);
                    $session->other = json_encode($client_details);
                    $session->save();

                    return TRUE;
                }else{
                    $step = $session->progress - 1;
                    $session->progress = $step;
                    $session->save();
                    return FALSE;
                }
                break;
            case 34:
                //veify if the PINs are equal
                $PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,33)->orderBy('id', 'DESC')->first();
                $CONFIRM_PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,34)->orderBy('id', 'DESC')->first();
                if($PIN->response == $CONFIRM_PIN->response){
                    //set PIN and send to Mifos
//                    $datatable = array(
//                        "PIN" => Crypt::encrypt($PIN->response),
//                        "locale"=>"en",
//                        "dateFormat"=> "dd MMMM yyyy"
//                    );
                    $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                    $client_details = json_decode($session->other);
//                    $r = MifosHelperController::setDatatable('PIN',$client_details->client_id,json_encode($datatable),$config);

//                    if (!empty($r->errors)) {
//
//                        if (strpos($r->defaultUserMessage, 'already exists')) {
//                            //we try to update
//                            $r = MifosHelperController::updateDatatable('PIN',$client_details->client_id,json_encode($datatable),$config,1);
//                        }
//                        if(!empty($r->errors)){
//                            $error_msg = 'We had a problem setting your PIN. Kindly retry or contact Customer Care';
//                            self::sendResponse($error_msg,1,$session);
//                        }
//                    }
                    // post the encoded application details
//                    $r = MifosHelperController::MifosPostTransaction($postURl, json_encode($datatable),$config);

                    //store PIN in session
                    $client_details->pin = Crypt::encrypt($PIN->response);
                    $session->other = json_encode($client_details);
                    $session->save();

                    $skipLogic = MifosUssdUserMenuSkipLogic::wherePhoneAndMifosUssdMenuId($session->phone,$session->menu_id)->first();
                    if(!$skipLogic){
                        $skipLogic = new MifosUssdUserMenuSkipLogic();
                    }
                    $skipLogic->phone = $session->phone;
                    $skipLogic->mifos_ussd_menu_id = $session->menu_id;
                    $skipLogic->skip = true;
                    $skipLogic->save();

                    $MifosSmsConfig = MifosSmsConfig::whereAppId(4)->first();
                    //send SMS
                    $msg = "You PIN has been sent successfully";
                    MifosSmsController::sendSms($session->phone,$msg,$MifosSmsConfig);
                    self::sendResponse($msg,2,$session);
                    return TRUE;
                }else{
                    $step = $session->progress - 1;
                    $session->progress = $step;
                    $session->save();
                    return FALSE;
                }
                break;
            case 39:
                //initiate and buy tokens
                $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                //validate national ID from Mifos
                $response = MifosHelperController::getClientByNationalId($message,$config);
                if(isset($response[0])){
                    if($response[0]->entityType == 'CLIENTIDENTIFIER'){
                        //check if ID belongs to the same client
                        $client = MifosHelperController::getClientbyClientId($response[0]->parentId,$config);
                        if(substr($client->mobileNo,-9) == (substr($session->phone,-9))){
                            $client_details = array('client_id'=>$response[0]->parentId,'external_id'=>$message);
                            $session->other = json_encode($client_details);
                            return TRUE;
                        }else{
                            $response = "National ID is valid but belongs to a different phone number.".PHP_EOL."Please enter your ID";
                            self::sendResponse($response,1,$session);
                        }
                    }else{
                        return FALSE;
                    }
                }
                break;
            default :
                break;
        }

    }

    public static function validatePIN($session,$message){
        $other_details = json_decode($session->other);
        if($message == Crypt::decrypt($other_details->pin)){
            $menu = MifosUssdMenu::find(3);
            $response = self::nextMenuSwitch($session,$menu);
//           print_r($menu);
//           exit;
        }else{
            $response = "Invalid PIN. Kindly Re enter your PIN";
        }
        self::sendResponse($response, 1, $session,null);
    }

    public static function ussdLog($session,$input,$type,$text){
        $MifosUssdLog = new MifosUssdLog();
        $MifosUssdLog->app_id = $session->app_id;
        $MifosUssdLog->phone = $session->phone;
        $MifosUssdLog->session_id = $input->sessionId;
        $MifosUssdLog->service_code = $input->serviceCode;
        $MifosUssdLog->text = $input->text;
        $MifosUssdLog->type = 0;
        $MifosUssdLog->save();
        return $MifosUssdLog;
    }

    public static function user_is_starting($text)
    {
        if (strlen($text) > 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public static function checkMenuSkipLogic($session,$menu){
        $skipLogic = MifosUssdUserMenuSkipLogic::wherePhoneAndMifosUssdMenuId($session->phone,$menu->id)->first();

        while($skipLogic->skip == 1) {
            $menu = MifosUssdMenu::find($menu->next_mifos_ussd_menu_id);
            $skipLogic = MifosUssdUserMenuSkipLogic::wherePhoneAndMifosUssdMenuId($session->phone,$menu->id)->first();
        }
        return $menu;
    }

    public static function getMenuItems($id){
        return MifosUssdMenuItems::whereMenuId($id)->get();
    }

    public static function nextMenuSwitch($mifos_ussd_session, $menu)
    {
        $menu = self::checkMenuSkipLogic($mifos_ussd_session,$menu);

        switch ($menu->type) {
            case 1:
                //continue to another menu
                $menu_items = self::getMenuItems($menu->id);
                $i = 1;
                $response = $menu->title . PHP_EOL;
                foreach ($menu_items as $key => $value) {
                    $response = $response . $i . ": " . $value->description . PHP_EOL;
                    $i++;
                }

                $mifos_ussd_session->menu_id = $menu->id;
                $mifos_ussd_session->menu_item_id = 0;
                $mifos_ussd_session->progress = 0;
                $mifos_ussd_session->session = 2;
                $mifos_ussd_session->save();
                //self::continueUssdMenu($user,$message,$menu);
                break;
            case 2:
                //start a process
                self::storeUssdResponse($mifos_ussd_session, $menu->id);
                $response = self::singleProcess($menu, $mifos_ussd_session, 1);
                return $menu->title.PHP_EOL.$response;
                break;
            case 3:
                //start a process
                self::storeUssdResponse($mifos_ussd_session, $menu->id);
                $response = self::singleProcess($menu, $mifos_ussd_session, 1);
                return $menu->title.PHP_EOL.$response;
                break;
            case 4:
                //start a process
                self::storeUssdResponse($mifos_ussd_session, $menu->id);
                $message = '';
                self::customApp($mifos_ussd_session,$menu,$message);
                break;
            case 5:
                //start a process
                self::storeUssdResponse($mifos_ussd_session, $menu->id);
                $response = $menu->confirmation_message;
                UnitTrustUssdHelperController::sendResponse($response,3,$mifos_ussd_session);
                break;
            default :
                self::resetUser($mifos_ussd_session,null);
                $response = "An authentication error occurred";
                break;
        }

        return $response;

    }

    public static function customApp($session,$menu,$message){


        switch ($menu->id) {
            case 6:
                $other = json_decode($session->other);
                $client_id = $other->client_id;
                $config = MifosUssdConfig::find($session->app_id);
                $message = "Dear {first_name}; to pay your fees go to Lipa na M-PESA >> Paybill >> Business No.: 4017901 >> Account No.: {prefix}{phone_number}. For assistance, call us on 0706247815 / 0784247815.";
                $client = MifosHelperController::getClientByClientId($client_id,$config);
                $search  = array('{first_name}','{prefix}','{phone_number}');
                $replace = array($client->firstname,"TAC","254".substr($session->phone,-9));
                $msg = str_replace($search, $replace, $message);
                $MifosSmsConfig = MifosSmsConfig::whereAppId(3)->first();
                //send SMS
                MifosSmsController::sendSMSViaConnectBind($session->phone,$msg,$MifosSmsConfig);
                self::sendResponse($msg,2,$session);
                break;
            case 8:
                $other = json_decode($session->other);
                $client_id = $other->client_id;
                $config = MifosUssdConfig::find($session->app_id);
                $loanAccounts = MifosHelperController::getClientLoanAccounts($client_id,$config);

                //repay Loan app
                if($session->progress == 1){
                    $i = 1;
                    foreach ($loanAccounts as $lA){
                        if($lA->status->id ==300 && $i==$message){
                            $message = "Dear {first_name}; pay at least {amount_due} via Lipa na M-PESA >> Paybill >> Business No.: 4017901 >> Account No.: {prefix}{phone_number}. For assistance, call us on 0706247815 / 0784247815.";
                            $client = MifosHelperController::getClientByClientId($client_id,$config);
                            $search  = array('{first_name}','{amount_due}','{prefix}','{phone_number}');
                            $replace = array($client->firstname,$lA->loanBalance,$lA->shortProductName,"254".substr($session->phone,-9));
                            $msg = str_replace($search, $replace, $message);
                            $MifosSmsConfig = MifosSmsConfig::whereAppId(3)->first();
                            //send SMS
                            MifosSmsController::sendSMSViaConnectBind($session->phone,$msg,$MifosSmsConfig);
                            break;
                        }
                        $i++;
                    }
                    self::sendResponse($msg,2,$session);
                }

                $response = $menu->title;
                $i = 1;
                foreach ($loanAccounts as $lA){
                    if($lA->status->id ==300){
                        $response = $response.PHP_EOL.$i.": ".$lA->shortProductName.$lA->id.":".$lA->loanBalance;
                        $i++;
                    }
                }
                $session->menu_id = $menu->id;
                $session->menu_item_id = 0;
                $session->progress = 1;
                $session->session = 6;
                $session->save();
                self::sendResponse($response,1,$session);
                break;
            case 9:
                //repay Loan app
                $other = json_decode($session->other);
                $client_id = $other->client_id;
                $config = MifosUssdConfig::find($session->app_id);
                $loanAccounts = MifosHelperController::getClientLoanAccounts($client_id,$config);

                $response = $menu->title;
                $i = 1;
                foreach ($loanAccounts as $lA){
                    if($lA->status->id ==300){
                        $response = $response.PHP_EOL.$i.": ".$lA->shortProductName.$lA->id.":".$lA->loanBalance;
                    }
                }
                self::sendResponse($response,1,$session);
                break;
            case 10:
                $other = json_decode($session->other);
                $client_id = $other->client_id;
                $config = MifosUssdConfig::find($session->app_id);
                $savingsAccount = MifosHelperController::getClientSavingsAccounts($client_id,$config);

                //repay Loan app
                if($session->progress == 1){
                    $i = 1;

                    foreach ($savingsAccount as $SA){
                        if($SA->status->id ==300 && isset($SA->accountBalance) && $message==$i){
                            $message = "Dear {first_name}; top up your savings by Lipa na M-PESA >> Paybill >> Business No.: 4017901 >> Account No.: {prefix}{phone_number}. For assistance, call us on 0706247815 / 0784247815.";
                            $client = MifosHelperController::getClientByClientId($client_id,$config);
                            $search  = array('{first_name}','{prefix}','{phone_number}');
                            $replace = array($client->firstname,$SA->shortProductName,"254".substr($session->phone,-9));
                            $msg = str_replace($search, $replace, $message);
                            $MifosSmsConfig = MifosSmsConfig::whereAppId(3)->first();
                            //send SMS
                            MifosSmsController::sendSMSViaConnectBind($session->phone,$msg,$MifosSmsConfig);
                            break;
                            $i++;
                        }
                    }


                    self::sendResponse($msg,2,$session);
                }

                $response = $menu->title;
                $i = 1;
                foreach ($savingsAccount as $SA){
                    if($SA->status->id ==300 && isset($SA->accountBalance)){
                        $response = $response.PHP_EOL.$i.": ".$SA->shortProductName.$SA->id.":".$SA->accountBalance;
                        $i++;
                    }
                }
                $session->menu_id = $menu->id;
                $session->menu_item_id = 0;
                $session->progress = 1;
                $session->session = 6;
                $session->save();
                self::sendResponse($response,1,$session);
                break;
//            case 19:
//                echo "hapa";
//                exit;
//                break;
            default :
//                self::resetUser($mifos_ussd_session,null);
                $response = "An authentication error occurred";
                break;
        }
    }

    public static function storeUssdResponse($session, $message)
    {
        $ussd_response = new MifosUssdResponse();
        $ussd_response->phone = $session->phone;
        $ussd_response->menu_id = $session->menu_id;
        $ussd_response->menu_item_id = $session->menu_item_id;
        $ussd_response->response = $message;
        $ussd_response->save();
        return $ussd_response;
    }

    public static function singleProcess($menu, $session, $step)
    {
        $menuItem = MifosUssdMenuItems::whereMenuIdAndStep($menu->id, $step)->first();
        if ($menuItem) {
            //update user data and next request and send back
            $session->menu_item_id = $menuItem->id;
            $session->menu_id = $menu->id;
            $session->progress = $step;
            $session->session = 2;
            $session->save();
            return $menuItem->description;

        }

    }

    public static function resetUser($mifos_ussd_session)
    {
        $mifos_ussd_session->app_id = $mifos_ussd_session->app_id;
        $mifos_ussd_session->session = 0;
        $mifos_ussd_session->progress = 0;
        $mifos_ussd_session->menu_id = 0;
        $mifos_ussd_session->difficulty_level = 0;
        $mifos_ussd_session->confirm_from = 0;
        $mifos_ussd_session->menu_item_id = 0;
        $mifos_ussd_session->save();
        return $mifos_ussd_session;
    }

    public static function sendResponse($response, $type, $session=null,$input=null)
    {

        if($session == null){
            $session->app_id = 0;
        }

        //Log response
        UnitTrustUssdHelperController::ussdLog($session,$input,0,$response);

        if ($type == 1) {
            $output = "CON ";
        } elseif ($type == 2) {
            $output = "CON ";
            $response = $response . PHP_EOL . "1. Back to main menu" . PHP_EOL . "2. Log out";
            $session->session = 4;
            $session->progress = 0;
            $session->save();
        } else {
            $output = "END ";
        }
        $output .= $response;
        header('Content-type: text/plain');
        echo $output;
        exit;
    }

    public static function continueUssdMenu($session, $message, $menu)
    {
        //verify response
        $menu_items = self::getMenuItems($session->menu_id);

        $i = 1;
        $choice = "";
        $next_menu_id = 0;
        foreach ($menu_items as $key => $value) {
            if (self::validationVariations(trim($message), $i, $value->description)) {
                $choice = $value->id;
                $next_menu_id = $value->next_menu_id;

                break;
            }
            $i++;
        }
        if (empty($choice)) {
            //get error, we could not understand your response
            $response = "We could not understand your response" . PHP_EOL;
            $i = 1;
            $response = $menu->title . PHP_EOL;
            foreach ($menu_items as $key => $value) {
                $response = $response . $i . ": " . $value->description . PHP_EOL;
                $i++;
            }

            return $response;
            //save the response
        } else {
            //there is a selected choice
            $menu = MifosUssdMenu::find($next_menu_id);
            //next menu switch
            $response = self::nextMenuSwitch($session, $menu);
            return $response;
        }

    }

    public static function validationVariations($message, $option, $value)
    {
        if ((trim(strtolower($message)) == trim(strtolower($value))) || ($message == $option) || ($message == "." . $option) || ($message == $option . ".") || ($message == "," . $option) || ($message == $option . ",")) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    public static function confirmGoBack($session, $message)
    {
        if (self::validationVariations($message, 1, "yes")) {
//            self::resetUser($session);
            $app = MifosUssdConfig::find($session->app_id);
            $mifos_ussd_session = self::resetUser($session,$app);
            $root_menu = MifosUssdMenu::whereAppIdAndIsRoot($app->id,1)->first();
            $response = self::nextMenuSwitch($mifos_ussd_session,$root_menu);
            self::sendResponse($response, 1, $mifos_ussd_session,$app);
        }else{
            $response = "Thank you for being our valued customer";
            self::sendResponse($response, 3, $session);

        }

    }

    public function confirmBatch($session, $menu)
    {
        //confirm this stuff
        $menu_items = self::getMenuItems($session->menu_id);
        if(!empty($menu->description)){
            $confirmation = "Confirm: " . $menu->description;
        }elseif(!empty($menu->confirmation_message)){
            $confirmation = "Confirm: " . $menu->confirmation_message;
        }else{
            $confirmation = "Confirm: " . $menu->title;
        }
        $amount = 0;
        foreach ($menu_items as $key => $value) {

            $response = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id, $value->id)->orderBy('id', 'DESC')->first();
            if (($value->confirmation_phrase == "Salary") || ($value->confirmation_phrase == "Amount")) {
                $amount = $response->response;
                $response->response = "Kshs. " . number_format($response->response, 2);
                $confirmation = $confirmation . PHP_EOL . $value->confirmation_phrase . ": " . $response->response;
            }elseif($value->confirmation_phrase != "IGNORE") {
                $confirmation = $confirmation . PHP_EOL . $value->confirmation_phrase . ": " . $response->response;
            }

        }
        $response = $confirmation . PHP_EOL . "1. Yes" . PHP_EOL . "2. No";

        $session->session = 3;
        $session->confirm_from = $session->menu_id;
        $session->save();

        return $response;
    }

    public function confirmUssdProcess($session, $message)
    {
        $menu = MifosUssdMenu::find($session->menu_id);
        if (self::validationVariations($message, 1, "yes")) {
            //if confirmed

            if (self::postUssdConfirmationProcess($session)) {
                $response = $menu->confirmation_message;
                if($menu->skippable == true){
                    $skipLogic = MifosUssdUserMenuSkipLogic::wherePhoneAndMifosUssdMenuId($session->phone,$menu->id)->first();
                    if(!$skipLogic){
                        $skipLogic = new MifosUssdUserMenuSkipLogic();
                    }
                    $skipLogic->phone = $session->phone;
                    $skipLogic->mifos_ussd_menu_id = $menu->id;
                    $skipLogic->skip = true;
                    $skipLogic->save();
                }
                //send SMS
                $MifosSmsConfig = MifosSmsConfig::whereAppId(4)->first();
                MifosSmsController::sendSms($session->phone,$response,$MifosSmsConfig);
                self::sendResponse($response,3,$session);
            } else {
                $response = "We had a problem processing your request. Please contact Customer Care on 0704 000 999";
            }

            self::resetUser($session);
            $MifosSmsConfig = MifosSmsConfig::whereAppId(3)->first();
            MifosSmsController::sendSMSViaConnectBind($session->phone,$response,$MifosSmsConfig);
            self::sendResponse($response, 2, $session);

        }elseif (self::validationVariations($message, 2, "no")) {
            if ($session->menu_id == 3) {
                $mifos_ussd_session = UnitTrustUssdHelperController::resetUser($session,$app);
                $root_menu = MifosUssdMenu::whereAppIdAndIsRoot($app->id,1)->first();
                $response = UnitTrustUssdHelperController::nextMenuSwitch($session,$root_menu);
                self::sendResponse($response, 1, $session);
            }


            $response = self::nextMenuSwitch($session, $menu);
            return $response;

        } else {
            //not confirmed
            $response = "Please enter 1 or 2";
            //restart the process
            $output = self::confirmBatch($session, $menu);

            $response = $response . PHP_EOL . $output;
            return $response;
        }


    }

    public function generateFakeToken(){
        $digits=20;
        $temp = "";

        for ($i = 0; $i < $digits; $i++) {
            $temp .= rand(0, 9);
        }
        return (int)$temp;
    }

    public function postUssdConfirmationProcess($session)
    {


        switch ($session->confirm_from) {
            case 3:
                //check if it is pc user

                if ($session->is_pcl_user == 1) {
                    return self::pclLoanApplication($session);
                }

                //check if there are any errors

                $notify = new NotifyController();
                $loanAccounts = self::getClientLoanAccounts($session->client_id);
                if (!is_numeric($loanAccounts[0])) {

                    $loan = self::getLoan($loanAccounts[0]->id);
                    $balance = self::getLoanBalance($session->client_id);
                    if (!empty($balance['amount'])) {
                        $error_msg = "Your outstanding Salary Advance Loan balance of Kshs. " . $balance['amount'] . " needs to be repaid before applying for a new Salary Advance Loan. For further assistance please call our customer care line: ".env('CUSTOMERCARE_NUMBER');
                        self::sendResponse($error_msg, 2, $session);
                    }
                    if ($loanAccounts[0]->status->pendingApproval == 1) {
                        $error_msg = "Your previous Salary Advance Loan application is pending approval. You will receive a confirmation SMS on disbursement of funds to your M-pesa account. For further assistance please call our customer care line: ".env('CUSTOMERCARE_NUMBER');
                        $notify->sendSms($session->phone_no, $error_msg);
                        self::sendResponse($error_msg, 2, $session);

                    }
                    if ($loanAccounts[0]->status->waitingForDisbursal == 1) {
                        $error_msg = "Your previous Salary Advance Loan application is pending approval. You will receive a confirmation SMS on disbursement of funds to your M-pesa account. For further assistance please call our customer care line: ".env('CUSTOMERCARE_NUMBER');
                        $notify->sendSms($session->phone_no, $error_msg);
                        self::sendResponse($error_msg, 2, $user);

                    }
                }

                //get the loan being applied for
                $loan = ussd_response::whereUserIdAndMenuIdAndMenuItemId($user->id, $user->menu_id, 7)->orderBy('id', 'DESC')->first()->response;
                $MifosX = new MifosXController();
                $data = $MifosX->applyLoan($user->client_id, $loan);

                if (empty($data->loanId)) {
                    $error = self::getLoanApplicationErrorMessage($data, $user);
                    $notify->sendSms($user->phone_no, $error);
                    //self::resetUser($user);
                    self::sendResponse($error, 2, $user);
                    exit;
                } else {
                    $now = Carbon::now()->toDateString();
                    $new_loan = $now.": New loan application from ".$user->name." amount ".number_format($loan,2).".";
                    $notify->sendSms("254707773267", $new_loan);
                    $notify->sendSms("254705099230", $new_loan);
                    return true;
                }
                break;
            case 9:

                //get the user
                $full_name = ussd_response::whereUserIdAndMenuIdAndMenuItemId($user->id, $user->menu_id, 10)->orderBy('id', 'DESC')->first()->response;
                $id = ussd_response::whereUserIdAndMenuIdAndMenuItemId($user->id, $user->menu_id, 11)->orderBy('id', 'DESC')->first()->response;
//                if($gender == 1){
//                    $gender = 'M';
//                }elseif($gender == 2){
//                    $gender = 'F';
//                }
//                $g = $gender;
//                if($gender == 'M'){
//                    $g = 1;
//                }elseif($gender == 'F'){
//                    $g = 2;
//                }
//                $dob = ussd_response::whereUserIdAndMenuIdAndMenuItemId($user->id, $user->menu_id, 13)->orderBy('id', 'DESC')->first()->response;
                $employer = ussd_response::whereUserIdAndMenuIdAndMenuItemId($user->id, $user->menu_id, 14)->orderBy('id', 'DESC')->first()->response;
                $salary = ussd_response::whereUserIdAndMenuIdAndMenuItemId($user->id, $user->menu_id, 15)->orderBy('id', 'DESC')->first()->response;

                $name = explode(" ", $full_name, 3);
                $reg_data = array();
                if (count($name) > 2) {
                    $reg_data['firstname'] = $name[0];
                    $reg_data['middlename'] = $name[1];
                    $reg_data['lastname'] = $name[2];
                } elseif (count($name) == 2) {
                    $reg_data['firstname'] = $name[0];
                    $reg_data['lastname'] = $name[1];
//                    $reg_data['lastname'] = $name[2];
                } else {
                    $reg_data['fullname'] = $full_name;
                }
                $reg_data['officeId'] = 1;
                $reg_data['externalId'] = $id;
                $reg_data['dateFormat'] = "dd MMMM yyyy";
                $reg_data['locale'] = "en";
//                $reg_data['genderId'] = $g;
//                $reg_data['clientTypeId'] = "individual";
//                $reg_data['legalFormType'] = "person";
//                $reg_data['Date of Birth'] = $dob;
                $reg_data['active'] = false;
                $reg_data['datatables'] = array(
                    ["registeredTableName" => "Employer",
                        "data" => array("Employer" => $employer)],
//                    ["registeredTableName"=>"DOB",
//                        "data" => array("DOB"=>$dob)],
//                    ["registeredTableName"=>"Gender",
//                        "data" => array("Gender"=>$gender)],
                    ["registeredTableName" => "Net Salary",
                        "data" => array(
                            "Net Salary" => $salary,
                            "locale" => "en",
                        )],
                );

                $reg_data['active'] = false;

                $reg_data['mobileNo'] = "254" . substr($user->phone_no, -9);

                // url for posting the application details
                $postURl = MIFOS_URL . "/clients?" . MIFOS_tenantIdentifier;
                // post the encoded application details
                $data = Hooks::MifosPostTransaction($postURl, json_encode($reg_data));
//                print_r($data);
//                exit;
                //datatables
                if (empty($data->clientId)) {

                    if (strpos($data->defaultUserMessage, 'already exists')) {

//                        $client = self::getUser($data->clientId);
//                        $user->terms_accepted = 1;
//                        $user->phone_no = $client->mobileNo;
//                        $user->terms_accepted_on = Carbon::now();
                        $user->delete();
                        $error_msg = 'A user with similar details has already been registered. Kindly redial to proceed';
                    } else {
                        $error_msg = 'We had a problem processing your registration. Kindly retry or contact Customer Care on 0704 000 999';
                    }
                    self::sendResponse($error_msg, 3, $user);
                } else {
                    $user->client_id = $data->clientId;
                    $user->save();
                    $no = substr($user->phone_no, -9);

                    $client = PreapprovedClients::where('mobile_number', "0" . $no)->orWhere('mobile_number', "254" . $no)->orWhere('mobile_number',$no)->first();
                    if($client){
                        //activate client
                        self::activateClient($user->client_id);
                    }

//                    //send identifier
                    $identifier = array(
                        "documentTypeId" => "2",
                        "documentKey" => $id,
                        "description" => "Document has been verified",
                        "status" => "active",
                    );
                    $postURl = MIFOS_URL . "/clients/" . $data->clientId . "/identifiers?" . MIFOS_tenantIdentifier;
                    // post the encoded application details
                    $data = Hooks::MifosPostTransaction($postURl, json_encode($identifier));
//                print_r($data);
//                exit;
                    $user = self::verifyPhonefromMifos(substr($user->phone_no, -9));
                    $user->terms_accepted = 1;
                    $user->terms_accepted_on = Carbon::now();
                    $user->save();
                    self::resetUser($user);
                    $menu = menu::find(9);
                    $response = "Dear " . $full_name . ", you will receive a confirmation SMS on activation. For further assistance please call our customer care line ".env('CUSTOMERCARE_NUMBER');
                    self::resetUser($user);
                    $notify = new NotifyController();
                    $notify->sendSms($user->phone_no, $response);
                    self::sendResponse($response, 3, $user);
                }
                break;
            case 17:
                //veify if the PINs are equal
                $PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,33)->orderBy('id', 'DESC')->first();

                $CONFIRM_PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,34)->orderBy('id', 'DESC')->first();
                if($PIN->response == $CONFIRM_PIN->response){
                    //set PIN and send to Mifos
                    $datatable = array(
                        "PIN" => Crypt::encrypt($PIN->response),
                        "locale"=>"en",
                        "dateFormat"=> "dd MMMM yyyy"
                    );
                    $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                    $client_details = json_decode($session->other);
                    $r = MifosHelperController::setDatatable('PIN',$client_details->client_id,json_encode($datatable),$config);

                    if (!empty($r->errors)) {

                        if (strpos($r->defaultUserMessage, 'already exists')) {
                            //we try to update
                            $r = MifosHelperController::updateDatatable('PIN',$client_details->client_id,json_encode($datatable),$config,1);
                        }
                        if(!empty($r->errors)){
                            $error_msg = 'We had a problem setting your PIN. Kindly retry or contact Customer Care';
                            self::sendResponse($error_msg,1,$session);
                        }
                    }
                    // post the encoded application details
//                    $r = MifosHelperController::MifosPostTransaction($postURl, json_encode($datatable),$config);

                    //store PIN in session
                    $client_details->pin = Crypt::encrypt($PIN->response);
                    $session->other = json_encode($client_details);
                    $session->save();

                    return TRUE;
                }else{
                    $step = $session->progress - 1;
                    $session->progress = $step;
                    $session->save();
                    return FALSE;
                }
                break;
            case 19:
                //veify if the PINs are equal
                $amount = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,38)->orderBy('id', 'DESC')->first()->response;
                $meter = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,39)->orderBy('id', 'DESC')->first()->response;

                $client_details = json_decode($session->other);
                if(isset($client_details->loan_balance)){
                    $limit = 100 - $amount;
                }else{
                    $limit = 100;
                }
                if($amount>$limit){
                    $response = 'Amount is greater than your remaining limit of KES '.$limit;
                }else{
                    $url = "http://146.185.176.230/api/twilight/meter-details?meter=".$meter;
                    $response = HelaplusTokensController::TokenGetTransaction($url,$session);
                    if(isset($response->customerName)){
                        $data = array();
                        $data['meter'] = $meter;
                        $data['customer_name'] = $response->customerName;
                        $data['amount'] = $amount;
                        $url = "http://146.185.176.230/api/twilight/purchase-tokens";
                        $response = HelaplusTokensController::TokenPostTransaction($url,json_encode($data),$session);
                        if(isset($response->data->token)){
                            $client_details = json_decode($session->other);
                            if(isset($client_details->loan_balance)){
                                $client_details->loan_balance = $client_details->loan_balance + $amount;
                            }else{
                                $client_details->loan_balance = $amount;
                            }
                            $session->other = json_encode($client_details);
                            $session->save();
                            $response = "Token generated successfully".$response->data->token.PHP_EOL." Your repayment is due on ".Carbon::now()->addMonth()->toDateString();
                            $MifosSmsConfig = MifosSmsConfig::whereAppId(4)->first();
                            MifosSmsController::sendSms($session->phone,$response,$MifosSmsConfig);
                            self::sendResponse($response,2,$session);
                        }else{
                            $token = self::generateFakeToken();
                            $response = "Your Token has generated successfully: ".$token.PHP_EOL." Your repayment is due on ".Carbon::now()->addMonth()->toDateString();
                            self::sendResponse($response,2,$session);
                        }
                    }else{
                        $response = "Invalid Meter Number";
                    }

                }
                self::sendResponse($response,2,$session);
                break;
            case 20:
                //veify if the PINs are equal
                $amount = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,40)->orderBy('id', 'DESC')->first()->response;
                STK::request($amount)
                    ->from($session->phone)
                    ->usingReference($session->phone,'GetPawa')
                    ->push();
                $msg = "You may also pay later by Lipa Na MPESA, PayBill 777784, Account ".$session->phone." amount KES ".$amount;
                $MifosSmsConfig = MifosSmsConfig::whereAppId(4)->first();
                MifosSmsController::sendSms($session->phone,$msg,$MifosSmsConfig);
                self::sendResponse("Kindly wait to enter your MPESA PIN to complete the transaction",3,$session);
                break;
            case 26:
                //veify if the PINs are equal
                $amount = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,50)->orderBy('id', 'DESC')->first()->response;
                STK::request($amount)
                    ->from($session->phone)
                    ->usingReference($session->phone,'ICEA Deposit')
                    ->push();
                $msg = "You may also pay later by Lipa Na MPESA, PayBill 777784, Account ".$session->phone." amount KES ".$amount;
                $MifosSmsConfig = MifosSmsConfig::whereAppId(4)->first();
                MifosSmsController::sendSms($session->phone,$msg,$MifosSmsConfig);
                self::sendResponse("Kindly wait to enter your MPESA PIN to complete the transaction",3,$session);
                break;
            default :
                return true;
                break;
        }

    }


}
