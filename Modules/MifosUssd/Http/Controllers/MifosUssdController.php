<?php

namespace Modules\MifosUssd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class MifosUssdController extends Controller
{


    public function app(Request $request)
    {

        $response = "Welcome to Hazina";
        $this->sendResponse($response,3);
        error_reporting(0);
        header('Content-type: text/plain');
        set_time_limit(100000);

        //get inputs
        $sessionId = $request->input('sessionId');
        $serviceCode = $request->input('serviceCode');
        $phoneNumber = $request->input('phoneNumber');
        $text = $request->input('text');   //

        print_r($phoneNumber);
        exit;
        $exploded_text = '';
        if (!empty($text)) {
            //getExtension URL
            $exploded_text = explode("*", $text, 2);
            $choice = $exploded_text[0];
        }


        $data = ['phone' => $phoneNumber, 'text' => $text, 'service_code' => $serviceCode, 'session_id' => $sessionId];

        //log USSD request
        ussd_logs::create($data);

        //verify that the user exists
        $no = substr($phoneNumber, -9);

        $user = UssdUser::where('phone_no', "0" . $no)->orWhere('phone_no', "254" . $no)->orWhere('email', "254" . $no)->first();

        if (!$user) {
            $user = self::verifyPhonefromMifos($no);
        }
        if (self::user_is_starting($text)) {

            //lets get the home menu
            //reset user
            if ($user) {
                self::resetUser($user);
            }

            //user authentication
            $message = '';
            $response = self::authenticateUser($user, $message);
            self::sendResponse($response, 1, $user);
        } else {
            //message is the latest stuff
            $result = explode("*", $text);
            if (empty($result)) {
                $message = $text;
            } else {
                end($result);
                // move the internal pointer to the end of the array
                $message = current($result);
            }
            //store ussd response


            //switch based on user session

            switch ($user->session) {

                case 0 :
                    //neutral user
                    break;
                case 1 :
                    //user authentication
                    $response = self::authenticateUser($user, $message);
                    break;
                case 2 :
                    $response = self::continueUssdProgress($user, $message);
                    //echo "Main Menu";
                    break;
                case 3 :
                    //confirm USSD Process
                    $response = self::confirmUssdProcess($user, $message);
                    break;
                case 4 :
                    //Go back menu
                    $response = self::confirmGoBack($user, $message);
                    break;
                case 5 :
                    //Go back menu
                    $response = self::resetPIN($user, $message);
                    break;
                case 6 :
                    //accept terms and conditions
                    $response = self::acceptTerms($user, $message);
                    break;
                default:
                    break;
            }

            self::sendResponse($response, 1, $user);
        }


    }

    public function sendResponse($response, $type, $user = null)
    {
        $sessionId = $_REQUEST["sessionId"];
        $serviceCode = $_REQUEST["serviceCode"];
        $phoneNumber = $_REQUEST["phoneNumber"];

        $data = ['phone' => $phoneNumber, 'text' => $response, 'service_code' => $serviceCode, 'session_id' => $sessionId];

        //log USSD request
//        ussd_logs::create($data);

        if ($type == 1) {
            $output = "CON ";
        } elseif ($type == 2) {
            $output = "CON ";
            $response = $response . PHP_EOL . "1. Back to main menu" . PHP_EOL . "2. Log out";
            $user->session = 4;
            $user->progress = 0;
            $user->save();
        } else {
            $output = "END ";
        }
        $output .= $response;
        header('Content-type: text/plain');
        echo $output;
        exit;
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
