<?php

namespace Modules\MifosSms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Modules\MifosSms\Entities\MifosSmsConfig;
use Modules\MifosSms\Entities\MifosSmsLog;

class MifosSmsController extends Controller
{
    public function index(){
        $config = MifosSmsConfig::whereAppId(3)->first();
        $response = self::sendSMSViaConnectBind('254728355429','this is a test',$config);
        print_r($response);
        exit;
    }
    public static function sendSms($to,$message,$config){
        $log = new MifosSmsLog();
        $log->app_id = $config->app_id;
        $log->gateway_id = $config->gateway_id;
        $log->phone = $to;
        $log->message = $message;
        $log->status = 0;
        $log->save();
        if($config->gateway_id == 1){

            $response = self::sendSmsViaWasiliana($to,$message,$config);
        }
        if($config->gateway_id == 2){
            $response =  self::sendSmsViaAT($to,$message,$config);
        }
        if($config->gateway_id == 3){
            $response = self::sendSMSViaConnectBind($to,$message,$config);
        }
        $log->content = $response;
        $log->save();

        return $response;

    }

    public static function sendSmsViaAT($to,$message,$config){
        $message = urlencode($message);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.africastalking.com/version1/messaging",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "username=$config->username&to=$to&message=$message&from=".$config->sender_name,
            CURLOPT_HTTPHEADER => array(
                "apikey: ".Crypt::decrypt($config->key),
                "Content-Type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);


//        $data = ['phone' => $to, 'message' => $message];
//
//        $gateway    = new AfricasTalkingGateway($config->username, Crypt::decrypt($config->key));
//
//        try
//        {
//            $results = $gateway->sendMessage($to, $message,$config->sender_name);
//
//        }
//        catch ( AfricasTalkingGatewayException $e )
//        {
//            $results = $e->getMessage();

//        }

        return $response;

    }

    public static function sendSmsViaWasiliana($to,$message,$config){
        $data = array();
        $data['recipients'] = array($to);
        $data['from'] = $config->sender_name;
        $data['message'] = $message;
        $url = 'https://api.wasiliana.com/api/v1/developer/sms/bulk/send/sms/request';
        $apiKey = "apiKey: ".Crypt::decrypt($config->key);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                $apiKey)
        );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        curl_close($ch);
        $dt = ['slug' => 'send_sms_response', 'content' => $data];
        $response = json_decode($data);
        return $response;
    }

    public static function sendSMSViaConnectBind($to,$message,$config){
        $no = substr($to,-9);
        $to = "254".$no;
        $url = "http://rslr.connectbind.com/bulksms/bulksms?username=".$config->username."&password=".Crypt::decrypt($config->key)."&type=0&dlr=1&destination=".$to."&source=".$config->sender_name."&message=".urlencode($message);
        $ch = curl_init();
        $data = ""; 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        $dt = ['slug' => 'send_sms_response', 'content' => $data];
        //log response
        curl_close($ch);
        return $data;
    }

}
