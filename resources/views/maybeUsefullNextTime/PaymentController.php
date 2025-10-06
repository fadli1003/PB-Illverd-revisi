<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Payment;
use Illmunate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function create(Request $request){
        $params = array(
            'transaction_details' => array(
                'order_id' => Str::uuid(),
                'gross_amount' => $request_price,
            ),
            'item_details' => array(
                'price' => $request->price,
                'quantity' => 1,
                'name' => $request->item_name
            ),
            'costumer_details' => array(
                'nama' => $request->costumer_name,
                'email' => $request->costumer_email
            ),
            'enable_payment' => array('credit_card', 'bca_va', 'bni_va', 'bri_va')
            
        );

        $auth = base64_encode(env('MIDTRANS_SERVER_KEY'));

        $response = Http::withHeader([
            'Content-Type' => 'application/json',
            'Authorization' => "Basic $auth",
        ])->post('https://app.sandbox.midtrans.com/snap/v1/transactions',$params);

        //save to db

        $payment = new Payment;
        $payment->order_id = $params['transaction_details']['order_id'];
        $payment->status = 'pending';
        $payment->price = $request->price;
        $payment->nama_pemesan = $request->costumer_name;
        $payment->costumer_email = $requets->costumer_email;
        $payment->keterangan = $request->item_name;
        $payment->checkout_link = $response->redirect_url;
        $payment->save();

        return response()->json($response);
    }

    public function webhook(Request $request){
        $auth = base64_encode(env('MIDTRANS_SERVER_KEY'));
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorizaton' => "Basic $auth",
        ])->get("https://api.sandbox.midtrans.com/v2/$request->order_id/status");

        $response = json_encode($response->body());

        $payment = Payment::where('order_id', $response->order_id)->firstOrFail();

        if ($payment->status=== 'settlement' || $payment->status === 'capture'){
            return response()->json('Pembayaran telah diproses');
        }

        if ($response->transaction_status === 'capture'){
            $payment->status = 'capture';
        }else if($response->transaction_status === 'settlement'){
            $payment->status = 'settlement';
        }else if($response->transaction_status === 'pending'){
            $payment->status = 'pending';
        }else if($response->transaction_status === 'deny'){
            $payment->status = 'deny';
        }else if($response->transaction_status === 'expire'){
            $payment->status = 'expire';
        }else if($response->transaction_status === 'cancel'){
            $payment->status = 'cancel';
        }
        $payment->save();

        return response()->json('success');
    }
}
