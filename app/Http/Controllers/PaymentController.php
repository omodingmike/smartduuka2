<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use IlluminateAgnostic\Str\Support\Str;

class PaymentController extends Controller
{
    private const BASE_URL = 'https://sandbox.momodeveloper.mtn.com';//https://sandbox.momodeveloper.mtn.com/collection/token/
//    private const CALLBACK_URL = 'https://95ab-154-227-128-2.ngrok-free.app/api/success';
    private const CALLBACK_URL = 'https://webhook.site/7359c0bd-d56a-4aec-9e7a-6c5180743760';
    //http://webhook.site/7359c0bd-d56a-4aec-9e7a-6c5180743760
//    private const KEY = '2f9867e903764c7ba59bd4cecfb9b209';
    private const KEY = 'ff24c79218bc422da76a8486c0388e8b'; // Disbursements
//    private const KEY = '60d79063182648f5b7f9b26cbf4db278';
    private const API_USER = 'c8a79e31-dad5-41c7-8273-394dee4f1881';
    private const API_KEY = 'e63f20b7265e4724abf25194fedef21b';

    // https://momodeveloper.mtn.com/api-documentation/testing
    public function pay(Request $request)
    {
        return $this->createApiUser();
    }


    public function createApiUser()
    {
        $api_user = Str::uuid()->toString();
        $response = Http::withHeaders([
            'X-Reference-Id'            => $api_user,
            'Ocp-Apim-Subscription-Key' => self::KEY,
        ])->post(self::BASE_URL . '/v1_0/apiuser', [
            'providerCallbackHost' => self::CALLBACK_URL,
        ]);
        if ($response->status() == 201) {
            info($api_user);
            return $this->createApiKey($api_user);
        } else {
            info('not created');
        }
    }

    public function getApiUser()
    {
        return Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => self::KEY,
        ])->get(self::BASE_URL . "/v1_0/apiuser/b6b18e4b-1442-4585-bba6-785c066fbc9d");
    }

    public function createApiKey($api_user)
    {
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => self::KEY,
        ])->post(self::BASE_URL . '/v1_0/apiuser/' . $api_user . '/apikey');
        if ($response->status() == 201) {
            return json_decode($response->body(), true)['apiKey'];
        }
    }

    public function createOauth2Token()
    {
        $api_user = Str::uuid()->toString();
        info($api_user);
        Http::withHeaders([
            'X-Reference-Id'            => $api_user,
            'Ocp-Apim-Subscription-Key' => self::KEY,
        ])->post(self::BASE_URL . '/v1_0/apiuser', [
            'providerCallbackHost' => self::CALLBACK_URL,
        ]);
        $api_key = ($this->createApiKey($api_user));
        $encoded = base64_encode($api_user . ':' . $api_key);
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization'             => "Basic $encoded",
                'X-Target-Environment'      => 'sandbox',
                'Ocp-Apim-Subscription-Key' => self::KEY,
                'Cache-Control'             => 'no-cache',
            ])->withOptions(['verify' => false])
            ->asForm()
            ->post(self::BASE_URL . '/collection/oauth2/token/',
                [
                    'grant_type'  => 'urn:openid:params:grant-type:ciba',
                    'auth_req_id' => '010001b2-5c84-4dd1-9eaf-56d5668dabd3'
                ]
            );
//        return json_decode($response->body(), true)['access_token'];
        return json_decode($response->body(), true);
    }

    public function accessToken()
    {
        $api_user = Str::uuid()->toString();
        Http::withHeaders([
            'X-Reference-Id'            => $api_user,
            'Ocp-Apim-Subscription-Key' => self::KEY,
        ])->post(self::BASE_URL . '/v1_0/apiuser', [
            'providerCallbackHost' => self::CALLBACK_URL,
        ]);
        $api_key = ($this->createApiKey($api_user));
        $encoded = base64_encode($api_user . ':' . $api_key);
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization'             => "Basic $encoded",
                'X-Target-Environment'      => 'sandbox',
                'Ocp-Apim-Subscription-Key' => self::KEY,
                'Cache-Control'             => 'no-cache',
            ])->withOptions(['verify' => false])
            ->post(self::BASE_URL . '/collection/token/');
//            ->post(self::BASE_URL . '/disbursement/token/');
        return json_decode($response->body(), true)['access_token'];
//        return json_decode($response->body(), true);
    }

    public function requestToPay()
    {
        $reference = Str::uuid()->toString();
        info('reference: ' . $reference);
//        $token = $this->createOauth2Token();
        $token = $this->accessToken();
        info('accessToken: ' . $token);
        $response = Http::withHeaders([
//            'X-Callback-Url'            => 'http://webhook.site', //http://webhook.site/7359c0bd-d56a-4aec-9e7a-6c5180743760
//            'X-Reference-Id'            => $reference,
            'X-Reference-Id'            => '16720858-63e6-45df-',
            'Authorization'             => "Bearer $token",
            'X-Target-Environment'      => 'sandbox',
            'Ocp-Apim-Subscription-Key' => self::KEY,
            'Content-Type'              => 'application/json',
        ])->post(
            self::BASE_URL . '/collection/v1_0/requesttopay',
//            self::BASE_URL . '/disbursement/v1_0/transfer',
            [
                'amount'       => '100.0',
                'currency'     => 'EUR',
                'externalId'   => $reference,
//                'payer'        => [
                'payee'        => [
                    "partyIdType" => "MSISDN",
                    'partyId'     => '46733123455',
                ],
                'payerMessage' => 'payerMessage',
                'payeeNote'    => 'payeeNote',
            ]
        );

        if ($response->status() == 202) {
            return $this->requesttoPayTransactionStatus($token, $reference);
        } else {
            return [
                'response' => $response->body(),
                'status'   => $response->status(),
            ];
        }
    }

    public function requesttoPayTransactionStatus($token, $reference)
    {
        $response = Http::withHeaders([
            'Authorization'             => "Bearer $token",
            'X-Target-Environment'      => 'sandbox',
            'Ocp-Apim-Subscription-Key' => self::KEY,
        ])->get(
//            self::BASE_URL . "/collection/v1_0/requesttopay/$reference"
            self::BASE_URL . "/disbursement/v1_0/transfer/$reference"
        );
        return $response->json();
    }

    public function validateAccountHolderStatus()
    {
        $response = Http::withHeaders([
            'Authorization'             => "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSMjU2In0.eyJjbGllbnRJZCI6IjYyMDljNDk3LTJiMmItNGYxMC1iM2E1LTEwM2UxNDEzMWJlOSIsImV4cGlyZXMiOiIyMDI0LTA2LTE0VDEyOjE5OjU5Ljg2NCIsInNlc3Npb25JZCI6IjQ2NTY4NTVkLTRlNDYtNDM0OC05NDc4LTFkODE0NmMyMzJlOCJ9.DJ2Ga1tTmWZxKjXaWfNsCVtRMRrj4uTskP0zCiygQiYmuwsiLTybVTAHfDg-c8pJYQBkTOTYHIkb4hOjO7ih6zZekmdkUOSDs2-52MGiHBM9Q03poeBgDH3I0Zv-MLtM3XdDO1tJDAS5llWi3CpD0R3MnfuSL6PqMwDX0Blm8bKifeF_gTzqKmrX2pAq6Snc9Ezaoo89l_ndbvAt_AUyeZQoXd1QXeJNS6lyxUYZ_9v1FzwTmA8ONJFJ7r4WANmJih-_vj3RgbSEmko5uM9hPvOy-0L8jw9g9iptfYiZEoWI0yyWITNpwuvHM6QRzPsTpM-GYAqhk6-kLPUUVaruEA",
            'X-Target-Environment'      => 'sandbox',
            'Ocp-Apim-Subscription-Key' => self::KEY,
        ])->get(
//            self::BASE_URL . "/disbursement/v1_0/accountholder/msisd/46733123456/active"
            self::BASE_URL . "/disbursement/v1_0/account/balance"
        );
        return [
            'response' => $response->body(),
            'status'   => $response->status(),
        ];
    }
}
