<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use App\Models\Appointment;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListMessages extends ListRecords
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment.doctor.user.name')
                ->label('Doctor Name')
                ->hidden(fn()=>Auth::user()->role === 'doctor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appointment.patient.user.name')
                ->label('Patient Name')
                ->hidden(fn()=>Auth::user()->role === 'patient')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appointment.appointment_date')
                    ->label('Appointment Date')
                    ->getStateUsing(fn($record) =>
                    // $record->appointment_id . ' - ' .

                        $record->appointment->appointment_date)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('doctor_message')
                    ->label("Doctor's Message")
                    ->searchable(),
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
                $user = User::find(Auth::user()->id);

                // If the user is an admin, they can see all messages from all doctors
                if ($user->hasRole('admin')) {
                    return $query;
                }

                // If the user is a doctor, only their given messages are shown
                if ($user->hasRole('doctor')) {
                    $appointments = Appointment::where('doctor_id', $user->doctor->id)->get();
                    if ($appointments->isNotEmpty()) {
                        $appointmentIds = $appointments->pluck('id');
                        return $query->whereIn('appointment_id', $appointmentIds);
                    }
                }

                // If the user is a patient, only their received messages are shown
                if ($user->hasRole('patient')) {
                    $appointments = Appointment::where('patient_id', $user->patient->id)->get();
                    if ($appointments->isNotEmpty()) {
                        $appointmentIds = $appointments->pluck('id');
                        return $query->whereIn('appointment_id', $appointmentIds);
                    }
                }
                //default
                return $query->whereRaw('1 = 0');
            })
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('View Messsage')
                        ->color('viewButton'),
                    Action::make('updateMessage')
                        ->hidden(fn() => Auth::user()->role === 'patient')
                        ->label("Edit Message")
                        ->form([
                            TextInput::make('doctor_message')
                                ->default(fn($record) => $record->doctor_message)
                                ->label("Doctor's Message")
                                ->required()
                        ])
                        ->color('yellow')
                        ->icon('heroicon-m-pencil-square')
                        ->action(function ($record, $data) {
                            $record->update([
                                'doctor_message' => $data['doctor_message'],
                            ]);
                            // $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
                            Notification::make()
                                ->title('Message updated')
                                ->success()
                                ->send();
                        })
                ])
                    ->tooltip('More Actions')
                    ->label('Actions')
                    ->icon('heroicon-s-cog')
                    ->size(ActionSize::Small)
                    ->color('action')
                    ->button(),
            ]);
    }
}
