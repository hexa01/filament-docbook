<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\AppointmentService;
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
        $appointment = $payment->appointment;
        $text = app(AppointmentService::class)->formatAppointmentAsReadableText($appointment);
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        Stripe\Charge::create([
            "amount" => 5 * 100,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => $text,
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
