<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\AppointmentService;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Stripe;

class StripeController extends Controller
{
    public function index(Payment $payment)
    {
        if (!$payment) {
            Notification::make()
                ->title('Payment not found')
                ->body('Payment not found!')
                ->danger()
                ->send();
            return redirect()->route('filament.admin.resources.payments.index')
                ->with('error', 'Payment not found!');
        }
        if ($payment->status == 'paid') {
            Notification::make()
                ->title('Error')
                ->body('Payment already done.')
                ->danger()
                ->send();
            return redirect()->route('filament.admin.resources.payments.index')->with('success', 'Payment already done!');
        }
        return view('checkout',compact('payment'));
    }


    public function createCharge(Request $request, Payment $payment)
    {
        $appointment = $payment->appointment;
        $text = app(AppointmentService::class)->formatAppointmentAsReadableText($appointment);
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        Stripe\Charge::create([
            "amount" => $payment->amount,
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
