<?php

namespace Modules\HelaplusTokens\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\HelaplusTokens\Entities\HelaplusRequestLog;

class HelaplusTokensController extends Controller
{


    public static function TokenGetTransaction($url,$session){

//        $data = ['slug' => $user->email.'_token_get_transaction', 'content' => $url];
        //log request
        $HelaplusRequestLog = new HelaplusRequestLog();
        $HelaplusRequestLog->slug = $session->phone.'_token_get_transaction';
        $HelaplusRequestLog->request = $url;
        $HelaplusRequestLog->save();

        $authorization = "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjkwZGYwNDRhZGFkMzk0NzBmNzRmYjQ0YmViMDUzMzAxNzkzZDcyNGU3ZGQwNGIxM2NiMjkxMGRlZWU5Y2E3ZDJmZGI5M2RmZGEwMzgxMTgzIn0.eyJhdWQiOiIyIiwianRpIjoiOTBkZjA0NGFkYWQzOTQ3MGY3NGZiNDRiZWIwNTMzMDE3OTNkNzI0ZTdkZDA0YjEzY2IyOTEwZGVlZTljYTdkMmZkYjkzZGZkYTAzODExODMiLCJpYXQiOjE1NjQ1Nzg1NjEsIm5iZiI6MTU2NDU3ODU2MSwiZXhwIjoxNTk2MTE0NTYxLCJzdWIiOiI0NiIsInNjb3BlcyI6W119.hrbO8msE57I0GJ8Bgqe26VjVBV6bAlDPnklHJS4ArIySJ1cnWWollSotDWNkh2QVzv5C-wv9OvHAzuCyMHGEcOiE_2DYNKc_IwY5wzreJ4bHSPC0RzLZxJvXKusINtMM1LFVYJl0iY9sc62-XaZicTLhKkIMWdsx5WuLXNPf3Xb62jMaQvlJ8NriHORtaanyhp9i-DyRuAhS0n4hpUCLAJ5FS_cTrSYxYewTQm1dFzgye7Hv2NzHSUkCVo95alxgYwy3j5DzbQdoj5xt5ZbS1XHvMhPAZ0HAwlAipVIna_ltLyFc7008yQ27lOWmbQBMgh9NXtrIs7pqUUGXA6q7ZU5dnohm8e75-Hqm02bUtIXeffj2yn8g3ZnvKevutWrNolGBvNnwi5SgHEiL4Dmd0RFeCzGoGlJ487oAwuq1nlyT92u3pEJfLqJjWEnY6fANyTcw_zWnX-UryXHYcMpMbVf2mhwsU5MiUmLvM-T2BH1121-7jD7HRDphLaq6Xycvl_70NMmgLn22-85GmkSQNCcJOQamQa5WGwwWxu_pHLbP-9dB2RPd7d6wC4dPb06eAYl_4dSea8mO7lfkij3gzVutF6jM8qk2yW-4tNuVDyuHTRl05PP8YHprlZXYONFiwYB6EUyupXcjcm8NqCkdZy6_9YH9ozViEI72XV5SNoI";
        $post_data="";
        $ch = curl_init();
        $data = "";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                $authorization)
        );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        $dt = ['slug' => 'token_get_response', 'content' => $data];
        $HelaplusRequestLog->response = $data;
        $HelaplusRequestLog->save();
        //log response
//        Log::create($dt);
        curl_close($ch);
        $response = json_decode($data);
        return $response;
    }
    public static function TokenPostTransaction($url,$post_data,$session){
        $HelaplusRequestLog = new HelaplusRequestLog();
        $HelaplusRequestLog->slug = $session->phone.'_token_get_transaction';
        $HelaplusRequestLog->request = $post_data;
        $HelaplusRequestLog->save();
        $authorization = "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjkwZGYwNDRhZGFkMzk0NzBmNzRmYjQ0YmViMDUzMzAxNzkzZDcyNGU3ZGQwNGIxM2NiMjkxMGRlZWU5Y2E3ZDJmZGI5M2RmZGEwMzgxMTgzIn0.eyJhdWQiOiIyIiwianRpIjoiOTBkZjA0NGFkYWQzOTQ3MGY3NGZiNDRiZWIwNTMzMDE3OTNkNzI0ZTdkZDA0YjEzY2IyOTEwZGVlZTljYTdkMmZkYjkzZGZkYTAzODExODMiLCJpYXQiOjE1NjQ1Nzg1NjEsIm5iZiI6MTU2NDU3ODU2MSwiZXhwIjoxNTk2MTE0NTYxLCJzdWIiOiI0NiIsInNjb3BlcyI6W119.hrbO8msE57I0GJ8Bgqe26VjVBV6bAlDPnklHJS4ArIySJ1cnWWollSotDWNkh2QVzv5C-wv9OvHAzuCyMHGEcOiE_2DYNKc_IwY5wzreJ4bHSPC0RzLZxJvXKusINtMM1LFVYJl0iY9sc62-XaZicTLhKkIMWdsx5WuLXNPf3Xb62jMaQvlJ8NriHORtaanyhp9i-DyRuAhS0n4hpUCLAJ5FS_cTrSYxYewTQm1dFzgye7Hv2NzHSUkCVo95alxgYwy3j5DzbQdoj5xt5ZbS1XHvMhPAZ0HAwlAipVIna_ltLyFc7008yQ27lOWmbQBMgh9NXtrIs7pqUUGXA6q7ZU5dnohm8e75-Hqm02bUtIXeffj2yn8g3ZnvKevutWrNolGBvNnwi5SgHEiL4Dmd0RFeCzGoGlJ487oAwuq1nlyT92u3pEJfLqJjWEnY6fANyTcw_zWnX-UryXHYcMpMbVf2mhwsU5MiUmLvM-T2BH1121-7jD7HRDphLaq6Xycvl_70NMmgLn22-85GmkSQNCcJOQamQa5WGwwWxu_pHLbP-9dB2RPd7d6wC4dPb06eAYl_4dSea8mO7lfkij3gzVutF6jM8qk2yW-4tNuVDyuHTRl05PP8YHprlZXYONFiwYB6EUyupXcjcm8NqCkdZy6_9YH9ozViEI72XV5SNoI";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                $authorization)
        );
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
//        print_r($data);exit;
        curl_close($ch);

        $HelaplusRequestLog->response = $data;
        $HelaplusRequestLog->save();
        //log response
//        Log::create($dt);

        $response = json_decode($data);

        return $response;
    }

}
