<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\Specialization;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class Stats extends BaseWidget
{
    protected function getStats(): array
    {
        $user = User::find(Auth::user()->id);
        $currentDay = Carbon::now()->format('l');
        $statsToDisplay = [];
        $availableDoctors = Schedule::where('day', $currentDay)
            ->whereTime('start_time', '<=', Carbon::now()->format('H:i'))
            ->whereTime('end_time', '>=', Carbon::now()->format('H:i'))
            ->distinct('doctor_id')->count();

        if ($user->role === 'admin') {
            $completed_appointments = Appointment::where('status', 'completed')->get();
            $upcoming_booked_appointments = Appointment::where('appointment_date', '>=', Carbon::parse(now()))->where('status', 'booked')->get();
            $statsToDisplay[] =   Stat::make('Total Patients', Patient::count())
                ->description(Patient::count() . ' registered patients')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->url(route('filament.admin.resources.users.index') . '?activeTab=Patients')
                ->extraAttributes([
                    'class' => 'transition transform hover:scale-105 hover:bg-green-100 rounded-lg',
                ]);

            $statsToDisplay[] = Stat::make('Total Doctors', Doctor::count())
                ->description(Doctor::count() . ' registered doctors')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('warning')
                ->url(route('filament.admin.resources.users.index') . '?activeTab=Doctors');
        } elseif ($user->role === 'patient') {
            $completed_appointments = Appointment::where('status', 'completed')->where('patient_id', $user->patient->id)->get();
            $upcoming_booked_appointments = Appointment::where('appointment_date', '>=', Carbon::parse(now()))->where('status', 'booked')->where('patient_id', $user->patient->id)->get();
            $statsToDisplay[] =
                Stat::make('Specializations', Specialization::all()->count())
                ->description(Specialization::count() . ' total specializations')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('primary')
                ->url(route('filament.admin.resources.specializations.index'));
        } elseif ($user->role === 'doctor') {
            $completed_appointments = Appointment::where('status', 'completed')->where('doctor_id', $user->doctor->id)->get();
            $upcoming_booked_appointments = Appointment::where('appointment_date', '>=', Carbon::parse(now()))->where('status', 'booked')->where('doctor_id', $user->doctor->id)->get();

        }


        $statsToDisplay[] =   Stat::make('Completed Appointments', $completed_appointments->count())
            ->description($completed_appointments->count() . ' appointments completed')
            ->descriptionIcon('heroicon-o-check')
            ->color('success')
            ->url(route('filament.admin.resources.appointments.index') . '?activeTab=Completed');

        $statsToDisplay[] =  Stat::make('Upcoming Booked Appointments', $upcoming_booked_appointments->count())
            ->description($upcoming_booked_appointments->count() . ' booked')
            ->descriptionIcon('heroicon-o-calendar')
            ->color('info')
            ->url(route('filament.admin.resources.appointments.index') . '?activeTab=Booked');

            if($user->role === 'doctor'){
                $yet_to_message_appointments = Appointment::query()->where('status', 'completed')->where('doctor_id', $user->doctor->id)->with(['patient', 'doctor'])->doesntHave('message')->get();
                $statsToDisplay[] =  Stat::make('Yet to Give Message', $yet_to_message_appointments->count())
                ->description($yet_to_message_appointments->count() . ' remaining')
                ->descriptionIcon('heroicon-o-envelope')
                ->color('warning')
                ->url(route('filament.admin.resources.messages.create'));
            }
        return $statsToDisplay;
        // Stat::make('Available Doctors', $availableDoctors)
        // ->description($availableDoctors . ' doctors available now')
        // ->descriptionIcon('heroicon-o-check-circle')
        // ->color('success')
        // ->url(route('filament.admin.resources.schedules.index')),



    }
}
