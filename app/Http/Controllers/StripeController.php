<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe;

class StripeController extends Controller
{
    public function index($payment)
    {
        return view('checkout',compact('payment'));
    }


    public function createCharge(Request $request, Payment $payment)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        Stripe\Charge::create([
            "amount" => 5 * 100,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => "Binaryboxtuts Payment Test"
        ]);
        $payment->update([
            'status' => 'paid',
            'payment_method' => 'stripe',
        ]);
        
        $appointment =  $payment->appointment;
        $appointment->status = 'booked';
        $appointment->save();
        return redirect()->route('filament.admin.resources.payments.index');
    }
}
