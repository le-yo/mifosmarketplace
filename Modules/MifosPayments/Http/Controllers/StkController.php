<?php

namespace App\Http\Controllers;

use App\ApiLog;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\JubileeTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\JubileeTransactionLog;
use App\Jobs\SubmitJubileeLoan;
use Illuminate\Http\JsonResponse;
use SmoDav\Mpesa\Laravel\Facades\STK;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\BadResponseException;

class StkController extends Controller
{
    protected $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new Client();

        $this->middleware('log.jubilee');
    }

    /**
     * Performs all infobip POST requests
     *
     * @param $endpoint
     * @param $body
     * @return mixed
     */
    public function infoBipPost($endpoint, $body)
    {
        $url = env('INFOBIP_BASE_URL').'/'.$endpoint;
        $credentials = env('INFOBIP_APIKEY');

        try {
            $clientParams = [
                'headers' => [
                    'Authorization' => 'Basic '.$credentials,
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($body)
            ];

            $data = $this->guzzleClient->post($url, $clientParams);

            $response = json_decode((string) $data->getBody(), true);
        } catch (BadResponseException $exception) {
            $response = json_decode((string) $exception->getResponse()->getBody()->getContents(), true);
        }

        ApiLog::query()->create([
            'request_url' => $url,
            'request_type' => 'POST',
            'request_body' => json_encode($body),
            'response_body' => json_encode($response),
            'source' => 'JUBILEE_INFOBIP'
        ]);

        return $response;
    }

    /**
     * Sends SMS text to the respective phone number
     *
     * @param $messageDetails
     * @return mixed
     */
    public function sendSMS($messageDetails)
    {
        return NotifyController::sendSmsViaAT($messageDetails['phone'],$messageDetails['message']);
    }

    /**
     * Sends payments instructions to client when STK fails
     *
     * @param $transaction
     */
    public function sendInstructionSMS($transaction)
    {
        $requestBody = ['to' => $transaction['phone'], 'text' => 'To successfully complete the IPF request kindly pay Kshs. '.number_format($transaction['amount']).' insurance downpayment to Mpesa Paybill Number: 708054, Account Number is '.$transaction['account_no'].'.'];

        $logCheck = ApiLog::query()
            ->where('source', 'LIKE', 'JUBILEE_INFOBIP')
            ->where('request_body', 'LIKE', json_encode($requestBody))
            ->where('created_at', 'LIKE', Carbon::today()->toDateString().'%')->first();

        // Only send if the SMS has not been sent to the same customer today
        if (!$logCheck) {
            self::sendSMS(['phone' => $requestBody['to'], 'message' => $requestBody['text']]);
        }
    }

    /**
     * Performs the STK push
     *
     * @param $transactionDetails
     * @return array
     */
    public function performSTK($transactionDetails)
    {
        $response = STK::request($transactionDetails['amount'])->from($transactionDetails['phone'])
            ->usingReference($transactionDetails['account_number'], $transactionDetails['description'])
            ->push();

        $data = [
            'code' => 200,
            'message' => $response->CustomerMessage,
            'data' => $response
        ];

        return $data;
    }

    /**
     * Initiates the STK push request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function initiateRequest(Request $request)
    {
        $token = $request->bearerToken();

        if($token == 'gp0IxrsZd8nBYJgPVLDEe1ayd6uooU0e') {
            $validator = Validator::make($request->all(), [
                'phone' => 'required',
                'unique_ref' => 'required',
                'amount' => 'required',
                'premium' => 'required',
                'description' => 'required',
                'account_number' => 'required'
            ]);

            if ($validator->fails()) {
                $errorResponse = [];
                $errors = array_map(function ($value) {
                    return implode(' ', $value);
                }, $validator->errors()->toArray());
                $errorKeys = $validator->errors()->keys();

                foreach ($errorKeys as $key) {
                    $array = ['field' => $key, 'error' => $errors[$key]];
                    array_push($errorResponse, $array);
                }

                return response()->json(['code' => Response::HTTP_BAD_GATEWAY, 'message' => $errorResponse, 'data' => [$request->all()]], Response::HTTP_BAD_GATEWAY);
            }

            try {
                $transactionCheck = JubileeTransaction::query()->where('account_no', '=', $request->input('account_number'))->first();

                if (!$transactionCheck) {
                    $stkData = self::performSTK($request->toArray());

                    JubileeTransaction::query()->create([
                        'phone' => $request->input('phone'),
                        'transaction_ref' => $request->input('unique_ref'),
                        'checkout_request_id' => $stkData['data']->CheckoutRequestID,
                        'account_no' => $request->input('account_number'),
                        'amount' => $request->input('amount'),
                        'premium' => $request->input('premium'),
                        'description' => $request->input('description')
                    ]);

                    return JsonResponse::create($stkData,200);
                } else {
                    if ($transactionCheck['status'] == 0) {
                        $retryStkData = self::performSTK($request->toArray());

                        $transactionCheck->update([
                            'phone' => $request->input('phone'),
                            'checkout_request_id' => $retryStkData['data']->CheckoutRequestID,
                            'account_no' => $request->input('account_number'),
                            'amount' => $request->input('amount'),
                            'premium' => $request->input('premium'),
                            'description' => $request->input('description')
                        ]);

                        return JsonResponse::create($retryStkData,200);
                    } else {
                        $data = [
                            'code' => 200,
                            'message' => 'The account number "'.$request->input('account_number').'" already exists and was successfully paid',
                            'data' => [
                                'MerchantRequestID' => '0000000000',
                                'CheckoutRequestID' => $transactionCheck['checkout_request_id'],
                                'ResponseCode' => '0',
                                'ResponseDescription' => 'Success. Request accepted for processing',
                                'CustomerMessage' => 'Success. Request accepted for processing',
                            ]
                        ];

                        return JsonResponse::create($data,200);
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error(json_encode([
                    'date' => Carbon::now()->toDateTimeString(),
                    'ref' => $request->get('unique_ref'),
                    'message' => json_decode($e->getMessage(), true),
                    'file' => json_decode($e->getFile(), true),
                    'line' => json_decode($e->getLine(), true),
                    'trace' => json_decode($e->getTraceAsString(), true)
                ]));

                $error = $e->getMessage();
                $data = [
                    'code' => 502,
                    'message' => $error,
                    'data' => $request->all()
                ];

                return JsonResponse::create($data,502);
            }
        } else {
            $data = [
                'code' => 401,
                'message' => 'Invalid API Key. Kindly ensure you use the correct Bearer Token',
                'data' => []
            ];

            return JsonResponse::create($data,401);
        }
    }

    /**
     * Checks the STK push transaction status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkTransaction(Request $request)
    {
        $token = $request->bearerToken();

        if($token == 'gp0IxrsZd8nBYJgPVLDEe1ayd6uooU0e') {
            $CheckoutRequestID = $request->input('CheckoutRequestID');

            if(!$CheckoutRequestID) {
                $data = [
                    'code' => 502,
                    'message' => 'CheckoutRequestID is required to validate a transaction',
                    'data' => $request->all()
                ];

                return JsonResponse::create($data,502);
            }

            $CheckoutRequestID = $request->input('CheckoutRequestID');

            try {
                $response = STK::validate($CheckoutRequestID);

                $transaction = JubileeTransaction::query()->where('checkout_request_id', '=', $CheckoutRequestID)->first();

                if(isset($response->errorMessage)) {
                    $response->ResultCode = "1030";
                    $data = [
                        'code' => 200,
                        'message' => $response->errorMessage,
                        'data' => $response
                    ];

                    self::sendInstructionSMS($transaction);

                    return JsonResponse::create($data,200);
                }

//                if($response->ResultCode == "0"){
//                    $code = "1";
//                }else{
//                    $code = $response->ResultCode;
//                }
                $data = [
                    'code' => 200,
                    'message' => $response->ResultDesc,
                    'data' => $response
                ];

                $clientErrors = ['Request cancelled by user', 'The balance is insufficient for the transaction'];

                if (in_array($response->ResultDesc, $clientErrors)) {
                    self::sendInstructionSMS($transaction);
                } else {
                    dispatch(new SubmitJubileeLoan([
                        'quotation_number' => $transaction['transaction_ref'],
                        'date_of_application' => Carbon::now()->toDateTimeString()
                    ]));

                    $transaction->update(['status' => 1]);
                }

                return JsonResponse::create($data,200);
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $data = [
                    'code' => 502,
                    'message' => $error,
                    'data' => $request->all()
                ];

                return JsonResponse::create($data,502);
            }
        } else {
            $data = [
                'code' => 401,
                'message' => 'Invalid API Key. Kindly ensure you use the correct Bearer Token',
                'data' => []
            ];

            return JsonResponse::create($data,401);
        }
    }

    /**
     * Checks deposits paid via SIM Toolkit
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkPaidTransactions(Request $request)
    {
        $token = $request->bearerToken();

        if($token == 'gp0IxrsZd8nBYJgPVLDEe1ayd6uooU0e') {
            try {
                if ($request->has('from')) {
                    $from = Carbon::today()->toDateString() . ' ' . $request->get('from').':00';
                } else {
                    $from = Carbon::now()->subMinutes(20)->toDateTimeString();
                }

                if ($request->has('to')) {
                    $to = Carbon::today()->toDateString() . ' ' . $request->get('to').':00';
                } else {
                    $to = Carbon::now()->toDateTimeString();
                }

                $transactionsQuery = JubileeTransaction::query()->where('type', '=', 'C2B')
                    ->where('is_deposit' , '=', 1)
                    ->where('updated_at', '>=', $from)
                    ->where('updated_at', '<=', $to)
                    ->get();

                if (count($transactionsQuery) > 0) {
                    $transactions = [];

                    foreach ($transactionsQuery->toArray() as $key => $value) {
                        $transactionLogQuery = JubileeTransactionLog::query()->where('content', 'LIKE', '%<BillRefNumber>'.$value['account_no'].'</BillRefNumber>%')->get()->last();

                        $transactionXml = new \DOMDocument();
                        $transactionXml->loadXML($transactionLogQuery['content']);

                        $array = [
                            'phone' => $value['phone'],
                            'account_number' => $value['account_no'],
                            'mpesa_ref' => $transactionXml->getElementsByTagName('TransID')->item(0)->nodeValue
                        ];

                        array_push($transactions, $array);
                    }

                    $data = [
                        'code' => 200,
                        'message' => 'Successfully pulled transactions for period '.$from.' - '.$to,
                        'data' => $transactions
                    ];
                } else {
                    $data = [
                        'code' => 200,
                        'message' => 'No data for the period '.$from.' - '.$to,
                        'data' => []
                    ];
                }

                return JsonResponse::create($data,Response::HTTP_OK);
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\Log::error(json_encode([
                    'date' => Carbon::now()->toDateTimeString(),
                    'message' => json_decode($exception->getMessage(), true),
                    'file' => json_decode($exception->getFile(), true),
                    'line' => json_decode($exception->getLine(), true),
                    'trace' => json_decode($exception->getTraceAsString(), true)
                ]));

                $data = [
                    'code' => 500,
                    'message' => 'Something went wrong. Please try again',
                    'data' => $request->all()
                ];

                return JsonResponse::create($data,Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $data = [
                'code' => 401,
                'message' => 'Invalid API Key. Kindly ensure you use the correct Bearer Token',
                'data' => []
            ];

            return JsonResponse::create($data,401);
        }
    }

    public function testATSms(){
        return NotifyController::sendSmsViaAT("254728355429","This is a test");
    }
}
