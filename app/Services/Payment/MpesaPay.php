<?php

namespace App\Services\Payment;

class MpesaPay
{

    private function getAccessToken()
    {

        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curlHandle = curl_init($url);

        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode(env('MPESA_CONSUMER_KEY') . ':' . env('MPESA_CONSUMER_SECRET'))]);

        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);

        $response = json_decode(curl_exec($curlHandle));

        curl_close($curlHandle);

        return $response->access_token;
    }

    private function getCallbackLink()
    {
        /**
         * Where to receive MPESA Callbacks
         */
        if (env('APP_ENV') == 'production') {
            return env('APP_URL') . '/api';
        } else {
            return env('MPESA_TEST_URL');
        }
    }

    private function makeRequest($url, $body)
    {

        $curlHandle = curl_init();

        curl_setopt_array($curlHandle, array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('Content-Type:application/json', 'Authorization: Bearer ' . $this->getAccessToken()),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
        ));

        $response = curl_exec($curlHandle);

        curl_close($curlHandle);

        return $response;
    }

    public function accountBalance()
    {
        $body = [
            'InitiatorName' => 'testapi',
            'SecurityCredential' => env('MPESA_B2C_PASSWORD'),
            'CommandID' => 'AccountBalance',
            'IdentifierType' => 2,
            'PartyA' => env('MPESA_SHORTCODE'),
            'Remarks' => 'Float Balance',
            'QueueTimeOutURL' => env('MPESA_TEST_URL') . '/ac-balance/queue',
            'ResultURL' => env('MPESA_TEST_URL') . '/ac-balance/callback',
        ];

        $url = 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query';

        $res = $this->makeRequest($url, $body);

        return $res;
    }

    public function reverseTransaction()
    {
        $body = [
            'Initiator' => env('MPESA_B2C_INITIATOR'),
            'SecurityCredential' => env('MPESA_B2C_PASSWORD'),
            'CommandID' => "TransactionReversal",
            'TransactionID' => "BRUNA6RDEI",
            'Amount' => "10",
            'ReceiverParty' => env('MPESA_SHORTCODE'),
            'RecieverIdentifierType' => "11",
            'Remarks' => 'ReversalRequest',
            'QueueTimeOutURL' => env('MPESA_TEST_URL') . '/reverse/queue',
            'ResultURL' => env('MPESA_TEST_URL') . '/reverse/callback',
            'Occasion' => 'ErroneosPayment',
        ];

        $url = 'https://sandbox.safaricom.co.ke/mpesa/reversal/v1/request';

        $res = $this->makeRequest($url, $body);

        return $res;
    }

    public function transactionStatus()
    {
        $body = [
            'InitiatorName' => env('MPESA_B2C_INITIATOR'),
            'SecurityCredential' => env('MPESA_B2C_PASSWORD'),
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => 'OEI2AK4Q16',
            'IdentifierType' => 1,
            'PartyA' => env('MPESA_SHORTCODE'),
            'PartyB' => env('MPESA_TEST_MSISDN'),
            'Remarks' => 'Paycheck',
            'QueueTimeOutURL' => env('MPESA_TEST_URL') . '/transaction/queue',
            'ResultURL' => env('MPESA_TEST_URL') . '/transaction/callback',
            'Occasion' => 'Weekendi',
        ];

        $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';

        $res = $this->makeRequest($url, $body);

        return $res;
    }

    public function b2c()
    {
        $body = [
            'InitiatorName' => env('MPESA_B2C_INITIATOR'),
            'SecurityCredential' => env('MPESA_B2C_PASSWORD'),
            'CommandID' => 'SalaryPayment',
            'Amount' => "3310",
            'PartyA' => env('MPESA_SHORTCODE'),
            'PartyB' => env('MPESA_TEST_MSISDN'),
            'Remarks' => 'Web Development',
            'QueueTimeOutURL' => env('MPESA_TEST_URL') . '/b2c/queue',
            'ResultURL' => env('MPESA_TEST_URL') . '/b2c/callback',
            'Occasion' => 'Work Payment',
        ];

        $url = 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';

        $res = $this->makeRequest($url, $body);

        return json_decode($res);
    }

    public function stkPush($amount, $phone, $accountReference, $transactionDescription)
    {
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $passKey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
        $timestamp = date('YmdHis');

        $body = [
            "BusinessShortCode" => env('MPESA_STK_SHORTCODE'),
            "Password" => base64_encode(env('MPESA_STK_SHORTCODE') . $passKey . $timestamp),
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => "1",
            "PartyA" => env('MPESA_TEST_MSISDN'),
            "PartyB" => env('MPESA_STK_SHORTCODE'),
            "PhoneNumber" => env('MPESA_TEST_MSISDN'),
            "CallBackURL" => $this->getCallbackLink() . '/stkpush',
            "AccountReference" => "PGC Payment",
            "TransactionDesc" => "OrderPayment",
        ];

        $response = $this->makeRequest($url, $body);

        return json_decode($response);
    }

    public function simulateTransaction()
    {
        $this->registerURLS();

        $body = [
            'ShortCode' => env('MPESA_SHORTCODE'),
            'Amount' => "10",
            'BillRefNumber' => '4321',
            'Msisdn' => env('MPESA_TEST_MSISDN'),
            'CommandID' => 'CustomerPayBillOnline',
        ];

        $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate';

        $response = $this->makeRequest($url, $body);

        return $response;
    }

    public function registerURLS()
    {

        /*
         *FOR C2B
         */
        $body = [
            'ShortCode' => env('MPESA_SHORTCODE'),
            'ResponseType' => 'Completed',
            'ConfirmationURL' => env('MPESA_TEST_URL') . '/confirmation',
            'ValidationURL' => env('MPESA_TEST_URL') . '/validation',
        ];

        $url = 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl';

        $response = $this->makeRequest($url, $body);

        return $response;
    }

}