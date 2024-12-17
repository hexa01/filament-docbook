<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;

class AppointmentService
{
    public function generateAvailableSlots($doctor, $appointment_date)
    {
        if (!$doctor || !$appointment_date) {
            return [];
        }
        $appointment_date = Carbon::parse($appointment_date);
        $appointment_day = $appointment_date->englishDayOfWeek;

        $schedule = $doctor->schedules->where('day', $appointment_day)->first();

        if (!$schedule) {
            return [];
        }
        $slots = $schedule->slots;
        $start_time = Carbon::parse($schedule->start_time);
        $available_slots = [];

        for ($i = 0; $i < $slots; $i++) {
            $available_slots[] = $start_time->format('H:i');
            $start_time->addMinutes(30);
        }
        $booked_slots = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $appointment_date)
            ->pluck('start_time')->toArray();
        return array_filter($available_slots, fn($slot) => !in_array($slot, $booked_slots));
    }

    public function optionsForStatusUpdate()
    {
        $user = User::find(Filament::auth()->user()->id);
        if ($user->hasRole('doctor')) {
            return [
                'completed' => 'Completed',
                'missed' => 'Missed',
            ];
        }

        if ($user->hasRole('admin')) {
            return [
                'pending' => 'Pending',
                'Booked' => 'Booked',
                'completed' => 'Completed',
                'missed' => 'Missed',
            ];
        }
    }

    public function formatAppointmentAsReadableText($appointment)
{
    $user = User::find(Filament::auth()->user()->id);

    // Initialize the formatted text variable
    $labeledText = '';

    // Format the appointment text based on the user's role
    if ($user->hasRole('doctor')) {
        $labeledText = "Appointment with {$appointment->patient->user->name} on {$appointment->appointment_date} at {$appointment->start_time}";
    }

    if ($user->hasRole('admin')) {
        $labeledText = "Appointment of {$appointment->patient->user->name} with Dr. {$appointment->doctor->user->name} on {$appointment->appointment_date} at {$appointment->start_time}";
    }

    return $labeledText;
}


public function formatAppointmentsAsReadableText($appointments)
{
    // Check if the input is a single appointment or a collection
    if (!$appointments instanceof \Illuminate\Support\Collection) {
        $appointments = collect([$appointments]); // Convert to a collection if it's a single appointment
    }

    // Initialize an empty array to hold the formatted appointment texts
    $labeledTexts = [];

    // Iterate over each appointment and format it using the existing method
    foreach ($appointments as $appointment) {
        $labeledTexts[$appointment->id] = $this->formatAppointmentAsReadableText($appointment);
    }

    return $labeledTexts;
}


    // public function formatAppointmentsAsReadableText($appointments)
    // {
    //     $user = User::find(Filament::auth()->user()->id);
    //     if ($user->hasRole('doctor')) {
    //         $labeledText = $appointments->mapWithKeys(function ($appointment) {
    //             return [
    //                 $appointment->id => "Appointment with {$appointment->patient->user->name} on {$appointment->appointment_date} at {$appointment->start_time}",
    //             ];
    //         })->toArray();
    //     }

    //     if ($user->hasRole('admin')) {
    //         $labeledText = $appointments->mapWithKeys(function ($appointment) {
    //             return [
    //                 $appointment->id => "Appointment of {$appointment->patient->user->name} with Dr. {$appointment->doctor->user->name} on {$appointment->appointment_date} at {$appointment->start_time}",
    //             ];
    //         })->toArray();
    //     }
    //     return $labeledText;

    // }
}
