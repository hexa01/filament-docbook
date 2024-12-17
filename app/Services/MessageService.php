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

    public function formatAppointmentsForDoctorMessage($appointments)
    {
        $user = User::find(Filament::auth()->user()->id);
        if ($user->hasRole('doctor')) {
            $labeledText = $appointments->mapWithKeys(function ($appointment) {
                return [
                    $appointment->id => "Appointment with {$appointment->patient->user->name} on {$appointment->appointment_date} at {$appointment->start_time}",
                ];
            })->toArray();
        }

        if ($user->hasRole('admin')) {
            $labeledText = $appointments->mapWithKeys(function ($appointment) {
                return [
                    $appointment->id => "{$appointment->patient->user->name} with Dr. {$appointment->doctor->user->name} on {$appointment->appointment_date} at {$appointment->start_time}",
                ];
            })->toArray();
        }
        return $labeledText;

    }
}
