<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialization;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';




    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Basic Appointment Information')
                    ->schema([
                        // Patient Selection
                        Select::make('patient_id')
                            ->label('Patient Name')
                            ->searchable()
                            ->preload()
                            ->options(
                                Patient::with('user')
                                    ->get()->pluck('user.name', 'id')
                            )
                            ->required(),

                        // Doctor Selection
                        Select::make('doctor_id')
                            ->label('Doctor Name')
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get) {
                                return Doctor::with('user')
                                    ->get()
                                    ->pluck('user.name', 'id');
                            })
                            ->required()
                            ->live(),

                        // Appointment Date
                        DatePicker::make('appointment_date')
                            ->required()
                            ->live()
                            ->minDate(Carbon::tomorrow())
                            ->native(false),

                        // Available Slots
                        Select::make('start_time')
                            ->label('Available Slots')
                            ->options(function (callable $get) {
                                $doctor = Doctor::find($get('doctor_id'));
                                $appointment_date = $get('appointment_date');

                                if (!$doctor || !$appointment_date) {
                                    return [];
                                }

                                $appointmentService = app(AppointmentService::class);
                                $availableSlots = $appointmentService->generateAvailableSlots($doctor, $appointment_date);
                                return collect($availableSlots)
                                    ->mapWithKeys(fn($slot) => [$slot => $slot])
                                    ->toArray();
                            })
                            ->required()
                            ->searchable(),
                    ])->columns(2),
                Forms\Components\Section::make('Appointment Status')
                    ->schema([
                        Forms\Components\Select::make('status')

                            ->options([
                                'completed' => 'Completed',
                                'missed' => 'Missed',
                            ])
                            ->required(),
                    ])->columns(2)
                    ->hidden(fn($get) => !$get('record') || !$get('record.id')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.user.name')
                    ->searchable()
                    ->label("Patient's Name")
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->searchable()
                    ->label("Doctor's Name")
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label("Slot"),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment.status')
                    ->label('Payment Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                // Get the currently authenticated user
                $user = User::find(Filament::auth()->user()->id);

                // If the user is an admin, they can see all appointments
                if ($user->hasRole('admin')) {
                    return $query;
                }

                // If the user is a doctor, only their appointments are shown
                if ($user->hasRole('doctor')) {
                    return $query->where('doctor_id', $user->doctor->id);
                }

                // If the user is a patient, only their appointments are shown
                if ($user->hasRole('patient')) {

                    if ($user->patient) {
                        return $query->where('patient_id', $user->patient->id);
                    } else {
                        return $query->whereRaw('1 = 0'); // If no patient relationship, show no appointments
                    }
                }

                //default
                return $query->whereRaw('1 = 0');
            })
            ->filters([
                //
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
