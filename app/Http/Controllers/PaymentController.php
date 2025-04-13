<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\{transaksi};

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function handleCallback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed == $request->signature_key) {
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                $order = transaksi::where('invoice', $request->order_id)->first();
                if ($order) {
                    $order->status_payment = 'Success';
                    $order->save();

                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
