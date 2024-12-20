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
        $availableDoctors = Schedule::where('day', $currentDay)
            ->whereTime('start_time', '<=', Carbon::now()->format('H:i'))
            ->whereTime('end_time', '>=', Carbon::now()->format('H:i'))
            ->distinct('doctor_id')->count();

        if ($user->role === 'patient') {
            $appointments = Appointment::where('appointment_date', '>=', Carbon::parse(now()))->where('status', 'booked')->where('patient_id', $user->patient->id)->get();
        }
        if ($user->role === 'doctor') {
            $appointments = Appointment::where('appointment_date', '>=', Carbon::parse(now()))->where('status', 'booked')->where('doctor_id', $user->doctor->id)->get();
        }
        if ($user->role === 'admin') {
            $appointments = Appointment::where('appointment_date', '>=', Carbon::parse(now()))->where('status', 'booked')->get();
        }

        return [
            // Stat::make('Total Users', User::count())
            //     ->description(User::count() . ' total users')
            //     ->descriptionIcon('heroicon-o-users')
            //     ->color('primary')
            //     ->url(route('filament.admin.resources.users.index')),

            Stat::make('Total Patients', Patient::count())
                ->description(Patient::count() . ' registered patients')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success')
                ->url(route('filament.admin.resources.users.index') . '?activeTab=Patients')
                ->extraAttributes([
                    'class' => 'transition transform hover:scale-105 hover:bg-green-100 rounded-lg',
                ]),

            Stat::make('Total Doctors', Doctor::count())
                ->description(Doctor::count() . ' active doctors')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('warning')
                ->url(route('filament.admin.resources.users.index') . '?activeTab=Doctors'),

            Stat::make('Specializations', Specialization::all()->count())
                ->description(Specialization::count() . ' total specializations')
                ->descriptionIcon('heroicon-o-briefcase')
                ->color('primary')
                ->url(route('filament.admin.resources.specializations.index')),

            Stat::make('Completed Appointments', Appointment::where('status', 'completed')->count())
                ->description(Appointment::where('status', 'completed')->count() . ' appointments completed')
                ->descriptionIcon('heroicon-o-check')
                ->color('success')
                ->url(route('filament.admin.resources.appointments.index') . '?activeTab=Completed'),

            Stat::make('Upcoming Booked Appointments', Appointment::where('appointment_date', '>=', Carbon::parse(now()))->where('status', 'booked')->count())
                ->description(Appointment::where('appointment_date', '>=', Carbon::parse(now()))->where('status', 'booked')->count() . ' today')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info')
                ->url(route('filament.admin.resources.appointments.index') . '?activeTab=Booked'),

            // Stat::make('Available Doctors', $availableDoctors)
            // ->description($availableDoctors . ' doctors available now')
            // ->descriptionIcon('heroicon-o-check-circle')
            // ->color('success')
            // ->url(route('filament.admin.resources.schedules.index')),




        ];
    }
}
