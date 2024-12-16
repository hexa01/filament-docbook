<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

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
}
