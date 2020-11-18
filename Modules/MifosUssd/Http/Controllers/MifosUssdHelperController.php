<?php

namespace Modules\MifosUssd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Modules\MifosHelper\Http\Controllers\MifosHelperController;
use Modules\MifosSms\Entities\MifosSmsConfig;
use Modules\MifosSms\Http\Controllers\MifosSmsController;
use Modules\MifosUssd\Entities\MifosUssdConfig;
use Modules\MifosUssd\Entities\MifosUssdLog;
use Modules\MifosUssd\Entities\MifosUssdMenu;
use Modules\MifosUssd\Entities\MifosUssdMenuItems;
use Modules\MifosUssd\Entities\MifosUssdResponse;
use Illuminate\Support\Facades\Validator;
use Modules\MifosUssd\Entities\MifosUssdSession;
use Modules\MifosUssd\Entities\MifosUssdUserMenuSkipLogic;

class MifosUssdHelperController extends Controller
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
    public function confirmLoanApplication($session,$message,$menuItem){

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
//
//        print_r($menuItem);
//        exit;
        if($menuItem->validation == 'custom'){
            if(self::customValidation($session,$message,$menuItem)){
            $step = $session->progress + 1;
            }
        }elseif(strpos($menuItem->validation, '_preset')) {

        if(self::presetValidation($session,$message,$menuItem)){
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
                    $product_id=7;
                    $syncDisbursementWithMeeting=true;
                }elseif($menuItem->id == 52){
                    $product_id=5;
                    $syncDisbursementWithMeeting=true;
                }else{
                    $product_id = 2;
                    $syncDisbursementWithMeeting=false;
                }
                //apply for the loan
                $response = MifosHelperController::applyLoan($product_id,$other->client_id,$amount,$config,$syncDisbursementWithMeeting);

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
            $session->progress = $step;
            $session->save();
            return $response. $menuItem->description;
        } else {

            if($menu->id == 1 || $menu->id == 28){

                $response = $menu->confirmation_message;
                $response = self::replaceTemplates($session,$response);
                $skipLogic = MifosUssdUserMenuSkipLogic::wherePhoneAndMifosUssdMenuId($session->phone,$menu->id)->first();
                if(!$skipLogic){
                $skipLogic = new MifosUssdUserMenuSkipLogic();
                }
                $skipLogic->phone = $session->phone;
                $skipLogic->mifos_ussd_menu_id = $menu->id;
                $skipLogic->skip = true;
                $skipLogic->save();
                //send SMS
                $MifosSmsConfig = MifosSmsConfig::whereAppId($session->app_id)->first();
                $sms_sending_response = MifosSmsController::sendSms($session->phone,$response,$MifosSmsConfig);

                self::sendResponse($response,3,$session);
            }else{
            $response = self::confirmBatch($session, $menu);
            }
            return $response;
        }
    }
    public static function presetValidation($session,$message,$menuItem){
        switch ($menuItem->validation) {
            case "nationalId_preset":

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
                    }elseif($response[0]->entityType == 'CLIENT'){
                        //check if ID belongs to the same client
                        $client = MifosHelperController::getClientbyClientId($response[0]->entityId,$config);

                        if(substr($client->mobileNo,-9) == (substr($session->phone,-9))){
                            $client_details = array('client_id'=>$response[0]->entityId,'external_id'=>$message);
                            $session->other = json_encode($client_details);
                            $session->save();
                            return TRUE;
                        }else{
                            $response = "National ID is valid but belongs to a different phone number.".PHP_EOL."Please enter your ID";
                            self::sendResponse($response,1,$session);
                        }
                    }{
                        return FALSE;
                    }
                }else{
                    $response = "National ID is not registered. Service only available to registered customers";
                    self::sendResponse($response,1,$session);
                }
                break;
            case "confirm_pin_preset":

                //veify if the PINs are equal
                $PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,54)->orderBy('id', 'DESC')->first();
                $CONFIRM_PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,55)->orderBy('id', 'DESC')->first();
//                print_r($PIN->response);
//                exit;
                if($PIN->response == $CONFIRM_PIN->response){
                    //set PIN and send to Mifos
                    $datatable = array(
                        "PIN" => Crypt::encrypt($PIN->response),
                        "locale"=>"en",
                        "dateFormat"=> "dd MMMM yyyy"
                    );
                    $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                    $client_details = json_decode($session->other);
                    $client_details->pin = Crypt::encrypt($PIN->response);
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
            case "auth_pin_preset":
                if($message == '0' && strlen($message)==1){
                    $menu = MifosUssdMenu::find(12);
                    $response = MifosUssdHelperController::nextMenuSwitch($session,$menu);
                    MifosUssdHelperController::sendResponse($response, 1, $session,null);
                }else{
                    $response = self::validatePIN($session,$message);
                }
                break;
            case "auth_nationalId_preset":
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
            case "auth2_nationalId_preset":
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
//                    print_r($PIN->response);
//                    exit;
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

            default :
                break;
        }
        return $response;
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
                $PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,3)->orderBy('id', 'DESC')->first();
                $CONFIRM_PIN = MifosUssdResponse::wherePhoneAndMenuIdAndMenuItemId($session->phone, $session->menu_id,6)->orderBy('id', 'DESC')->first();
//                print_r($CONFIRM_PIN->response);
//                exit;
                if($PIN->response == $CONFIRM_PIN->response){
                    //set PIN and send to Mifos
                    $datatable = array(
                        "PIN" => Crypt::encrypt($PIN->response),
                        "locale"=>"en",
                        "dateFormat"=> "dd MMMM yyyy"
                    );
                    $config = MifosUssdConfig::whereAppId($session->app_id)->first();
                    $client_details = json_decode($session->other);
                    $client_details->pin = Crypt::encrypt($PIN->response);
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
                if($message == '0' && strlen($message)==1){
                    $menu = MifosUssdMenu::find(12);
                    $response = MifosUssdHelperController::nextMenuSwitch($session,$menu);
                    MifosUssdHelperController::sendResponse($response, 1, $session,null);
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
//                    print_r($PIN->response);
//                    exit;
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

            default :
                break;
        }

    }

    public function validatePIN($session,$message){

        $other_details = json_decode($session->other);
        if(isset($other_details->pin)){

        if($message == Crypt::decrypt($other_details->pin)){
           $menu = MifosUssdMenu::find($session->menu_id+1);
           $response = self::nextMenuSwitch($session,$menu);
//           print_r($menu);
//           exit;
        }else{
            $response = "Invalid PIN. Kindly Re enter your PIN";
        }

        }else{
            $menu = MifosUssdMenu::find(12);
            $response = "In order to proceed:".PHP_EOL.MifosUssdHelperController::nextMenuSwitch($session,$menu);
            MifosUssdHelperController::sendResponse($response, 1, $session,null);
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

    public function getMenuItems($id){
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
                MifosUssdHelperController::sendResponse($response,3,$mifos_ussd_session);
                break;
            case 6:
                //Get the products
                self::storeUssdResponse($mifos_ussd_session, $menu->id);
                $mifos_ussd_session->progress = 1;
                $mifos_ussd_session->save();
                $response = self::loanApplicationProcess($mifos_ussd_session,"");
                $response = $menu->title.PHP_EOL.$response;
                break;
            default :
                self::resetUser($mifos_ussd_session,null);
                $response = "An authentication error occurred";
                break;
        }

        return $response;

    }

    public function loanApplicationProcess($session,$message){


        switch ($session->progress) {
            case 1:
                //fetch loan products
                $config = MifosUssdConfig::find($session->app_id);
                $loanProducts = MifosHelperController::getLoanProducts($config);
                $i = 1;
                $response = "";
                foreach ($loanProducts as $lP){
                    $response = $response . $i . ": " . $lP->name . PHP_EOL;
                    $i++;
                }
                $session->progress = 2;
                $session->session = 7;
                $session->save();
                break;
            case 2:
                echo "hapa sasa";
                exit;
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
                MifosUssdHelperController::sendResponse($response,3,$mifos_ussd_session);
                break;
            case 6:
                //Get the products
                self::storeUssdResponse($mifos_ussd_session, $menu->id);
                $response = self::loanApplicationProcess($menu, $mifos_ussd_session, 1);
                $response = $menu->confirmation_message;
                MifosUssdHelperController::sendResponse($response,3,$mifos_ussd_session);
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
            default :
//                self::resetUser($mifos_ussd_session,null);
                $response = "An authentication error occurred";
                break;
        }
    }

    public function storeUssdResponse($session, $message)
    {
        $ussd_response = new MifosUssdResponse();
        $ussd_response->phone = $session->phone;
        $ussd_response->menu_id = $session->menu_id;
        $ussd_response->menu_item_id = $session->menu_item_id;
        $ussd_response->response = $message;
        $ussd_response->save();
        return $ussd_response;
    }

    public function singleProcess($menu, $session, $step)
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

    public function replaceTemplates($session,$response){
        $config = MifosUssdConfig::find($session->app_id);
        $search  = array('{app_name}','{app_ussd}');
        $replace = array(ucwords($config->app_name),$config->ussd_code);
        $response = str_replace($search, $replace, $response);
        return $response;
    }

    public static function sendResponse($response, $type, $session=null,$input=null)
    {

        if($session == null){
            $session->app_id = 0;
        }

        $response = self::replaceTemplates($session,$response);
        //Log response
        MifosUssdHelperController::ussdLog($session,$input,0,$response);

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
            $menu = MifosUssdMenu::find(3);

            $response = MifosUssdHelperController::nextMenuSwitch($session,$menu);
            $session->session = 2;
            $session->menu_id = $menu->id;
            $session->menu_item_id = 0;
            $session->progress = 0;
            $session->save();
            self::sendResponse($response, 1, $session);
        }else{
            $response = "Thank you for being our valued customer";
            self::sendResponse($response, 3, $session);

        }

    }

}
