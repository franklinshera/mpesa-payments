<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Payment\MpesaPay;
use Exception;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function pay(Request $request, MpesaPay $mpesaPay)
    {

        $payingNumber = env('MPESA_TEST_MSISDN');
        $order = (object) ['id' => random_int(100, 10000), 'serial' => 'SKU' . random_int(10000, 1000000), 'cost' => random_int(200, 10000)];

        try {

            $payResponse = $mpesaPay->stkPush(1, $payingNumber, $order->serial, "OrderPayment");

            Transaction::create(
                [
                    'order_id' => $order->serial,
                    'MerchantRequestID' => $payResponse->MerchantRequestID,
                    'CheckoutRequestID' => $payResponse->CheckoutRequestID,
                    'Amount' => $order->cost,
                ]
            );

            return $payResponse;

        } catch (Exception $e) {
            return $e;
        }

    }
}