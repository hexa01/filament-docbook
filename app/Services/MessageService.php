<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Filament\Facades\Filament;

class MessageService
{

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getCompletedAppointments()
    {
        $user = User::find(Filament::auth()->user()->id);
        if ($user->hasRole('patient')) {
            $appointments = Appointment::query()->where('status', 'completed')->where('patient_id', $user->patient->id)->with(['patient', 'doctor'])->doesntHave('message')->get();
        }
        if ($user->hasRole('doctor')) {
            $appointments = Appointment::query()->where('status', 'completed')->where('doctor_id', $user->doctor->id)->with(['patient', 'doctor'])->doesntHave('message')->get();
        }
        if ($user->hasRole('admin')) {
            $appointments = Appointment::query()->where('status', 'completed')->with(['patient', 'doctor'])->doesntHave('message')->get();
        }
        return $appointments;
    }

}
