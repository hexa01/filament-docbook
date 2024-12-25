<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class AppointmentTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    protected static ?string $icon = 'heroicon-o-calendar';

    protected static ?string $heading = 'Upcoming Appointments';

    public function table(Table $table): Table
    {
        $today = Carbon::today();
        $nextMonth = Carbon::today()->addMonth(30);
        $user = User::find(Auth::user()->id);  // Get the currently authenticated user

        // Base query to filter appointments within the next week
        $query = Appointment::query()
            ->whereBetween('appointment_date', [$today, $nextMonth]);

        // If the user is an admin, show all appointments
        if ($user->hasRole('admin')) {
            $query->whereIn('status', ['booked', 'pending']);
            // No additional filtering needed for admins
        }

        // If the user is a doctor, filter appointments where the doctor is associated
        elseif ($user->hasRole('doctor')) {
            $query->where('doctor_id', $user->doctor->id)->where('status','booked');  // Assuming the user has a `doctor` relationship
        }

        // If the user is a patient, filter appointments where the patient is associated
        elseif ($user->hasRole('patient')) {
            $query->where('patient_id', $user->patient->id)->whereIn('status', ['booked', 'pending']);  // Assuming the user has a `patient` relationship
        }


        return $table
            ->query($query)
            ->columns([
                TextColumn::make('patient.user.name')
                    ->label('Patient Name')
                    ->searchable(),
                TextColumn::make('doctor.user.name')
                    ->label('Doctor Name')
                    ->hidden(fn() => $user->role === 'doctor'),
                TextColumn::make('appointment_date')
                ->label('Appointment Date'),
                TextColumn::make('slot'),
                TextColumn::make('status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'completed' => 'success',
                    'pending' => 'warning',
                    'missed' => 'danger',
                    'booked' => 'yellow',
                    default => 'secondary'
                }),
            ])->defaultSort(function ($query) {
                $query->orderByRaw("
                    CASE
                        WHEN status = 'pending' THEN 1
                        WHEN status = 'booked' THEN 2
                        WHEN status = 'completed' THEN 3
                        WHEN status = 'missed' THEN 4
                        ELSE 5
                    END
                ");
            })
            ;
    }
}
