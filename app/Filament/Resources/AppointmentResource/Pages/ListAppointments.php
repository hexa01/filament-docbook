<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        $user = User::find(Auth::user()->id);
        return $table
            ->columns([
                TextColumn::make('patient.user.name')
                    ->searchable()
                    ->label("Patient's Name")
                    ->sortable()
                    ->hidden(fn() => $user->role === 'patient'),
                TextColumn::make('doctor.user.name')
                    ->searchable()
                    ->label("Doctor's Name")
                    ->sortable()
                    ->hidden(fn() => $user->role === 'doctor'),
                TextColumn::make('appointment_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('slot')
                    ->label("Slot"),
                TextColumn::make('status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'completed' => 'success',
                    'pending' => 'warning',
                    'missed' => 'danger',
                    'booked' => 'yellow',
                    default => 'secondary'
                })
                ->searchable(),
                // TextColumn::make('payment.status')
                //     ->label('Payment Status')
                //     ->badge()
                //     ->color(fn(string $state): string => match ($state) {
                //         'paid' => 'success',
                //         'unpaid' => 'danger',
                //         default => 'secondary'
                //     })
                //     ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('status','asc')->defaultSort('appointment_date','asc')
            ->modifyQueryUsing(function (Builder $query) {
                // Get the currently authenticated user
                $user = User::find(Auth::user()->id);

                // If the user is an admin, they can see all appointments
                if ($user->hasRole('admin')) {
                    return $query;
                }

                // If the user is a doctor, only their appointments are shown
                if ($user->hasRole('doctor')) {
                    return $query->where('doctor_id', $user->doctor->id)->where('status', '!=', 'pending');
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
                Filter::make('appointment_date')
                    ->label('Appointment Date')
                    ->form([
                        DatePicker::make('appointment_date')
                    ])->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['appointment_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('appointment_date', '=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! isset($data['date']) || ! $data['date']) {
                            return null;
                        }

                        // Display the selected date in a user-friendly format
                        return 'Appointment on ' . Carbon::parse($data['date'])->toFormattedDateString();
                    }),

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'booked' => 'Booked',
                        'completed' => 'Completed',
                        'missed' => 'Missed',
                    ]),
            ])

            ->actions([

                ActionGroup::make([
                    ViewAction::make()
                    ->label('View Appointment'),
                    Action::make('markCompleted')
                        ->label('Mark as Completed')
                        ->icon('heroicon-s-check-circle')
                        // ->hidden(fn() => $user->role === 'patient')
                        ->requiresConfirmation()
                        ->hidden(fn($record) => $record->status === 'completed' ||
                            $record->status === 'missed'
                            || Carbon::parse($record->appointment_date)->isFuture()
                            || $user->role === 'patient'
                            )
                        ->color(function ($record) {
                            if (
                                $record->status === 'completed' || $record->status === 'missed'
                                || Carbon::parse($record->appointment_date)->isFuture()
                            ) {
                                return 'gray';
                            }
                            return 'success';
                        })
                        ->action(function ($record) {
                            $record->update(['status' => 'completed']);
                            $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
                            Notification::make()
                                ->title('Appointment Completed')
                                ->success()
                                ->body("$text has been marked as completed.")
                                ->send();
                        }),
                    Action::make('markMissed')
                        ->label('Mark as Missed')
                        ->icon('heroicon-s-x-circle')
                        ->requiresConfirmation()
                        // ->hidden(fn() => $user->role === 'patient')
                        ->hidden(fn($record) => $record->status === 'completed' ||
                            $record->status === 'missed' ||
                            Carbon::parse($record->appointment_date)->isFuture()
                            || $user->role === 'patient'
                            )
                        ->action(function ($record) {
                            $record->update(['status' => 'missed']);
                            $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
                            Notification::make()
                                ->title('Appointment Marked as Missed')
                                ->success()
                                ->body("$text has been marked as missed.")
                                ->send();
                        })
                        ->color(function ($record) {
                            if ($record->status === 'completed' || $record->status === 'missed' ||
                            Carbon::parse($record->appointment_date)->isFuture()) {
                                return 'gray';
                            }
                            return 'danger';
                        }),
                    EditAction::make()
                        ->disabled(function ($record) {
                            return (Auth::user()->role === 'admin' && $record->status === 'completed') ||
                                (Auth::user()->role === 'patient' && $record->status !== 'pending');
                        })
                        ->color(function ($record) {
                            if ((Auth::user()->role === 'admin' && $record->status === 'completed') ||
                                (Auth::user()->role === 'patient' && $record->status !== 'pending')
                            ) {
                                return 'gray';
                            }
                            return 'yellow';
                        })
                        // ->disabled(fn($record) =>  (Auth::user()->role === 'admin' && $record->status === 'completed'))
                        // ->hidden(fn($record) => Auth::user()->role === 'patient' && $record->status !== 'pending')
                        ->label('Edit Appointment'),
                    DeleteAction::make()
                        ->label('Delete Appointment')
                        ->disabled(function ($record) {
                            return (Auth::user()->role === 'admin' && $record->status === 'completed') ||
                                (Auth::user()->role === 'patient' && $record->status !== 'pending');
                        })
                        ->before(function ($record, DeleteAction $action) {
                            if (Auth::user()->role === 'admin' && $record->status === 'completed') {
                                Notification::make()
                                    ->danger()
                                    ->title('Appointment not deleted')
                                    ->body('You can\'t delete appointment that is already completed.')
                                    ->send();
                                $action->cancel();
                            } elseif (Auth::user()->role !== 'admin' && $record->status != 'pending') {
                                Notification::make()
                                    ->danger()
                                    ->title('Appointment not deleted')
                                    ->body("You can't delete appointment that is already $record->status.")
                                    ->send();
                                $action->cancel();
                            }
                        })
                        ->successNotification(function ($record) {
                            $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
                            return Notification::make()
                                ->success()
                                ->icon('heroicon-o-trash')
                                ->title('Appointment Removed!')
                                ->body("$text has been removed.");
                        }),
                ])

                    ->tooltip('More Actions')
                    ->label('Actions')
                    ->icon('heroicon-s-cog')
                    ->size(ActionSize::Small)
                    ->color('purple')
                ->button()
            ])
            // ->bulkActions([
            //     BulkAction::make('markCompleted')
            //         ->label('Mark as Completed')
            //         ->color('success')
            //         ->icon('heroicon-s-check-circle')
            //         ->action(function (array $records) {
            //             foreach ($records as $record) {
            //                 // Update each record to "completed"
            //                 $record->update(['status' => 'completed']);
            //             }
            //             Notification::make()
            //                 ->title('Appointments Status Updated')
            //                 ->success()
            //                 ->body("Selected Appointments have been marked as completed.")
            //                 ->send();
            //         }),
            //     BulkAction::make('markMissed')
            //         ->label('Mark as Missed')
            //         ->color('danger')
            //         ->icon('heroicon-s-x-circle')
            //         ->action(function (array $records) {
            //             foreach ($records as $record) {
            //                 // Update each record to "missed"
            //                 $record->update(['status' => 'missed']);
            //             }

            //             // Send notification after updating statuses
            //             Notification::make()
            //                 ->title('Appointments Status Updated')
            //                 ->success()
            //                 ->body("Selected Appointments have been marked as missed.")
            //                 ->send();
            //         }),

            // ]),
            ;
    }

    public function getTabs(): array
    {
        // Get the currently authenticated user
        $user = Auth::user();

        // Initialize the tabs array
        $tabs = [];

        if ($user->role == 'doctor') {
            // Get the doctor's ID based on the user
            $doctorId = $user->doctor->id;

            // Define tabs for doctor
            $tabs = [
                'All' => Tab::make()
                    ->badge(Appointment::where('doctor_id', $doctorId)->count())
                    ->icon('heroicon-s-ellipsis-horizontal-circle'),

                'Booked' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('doctor_id', $doctorId)->where('status', 'booked'))
                    ->badge(Appointment::where('doctor_id', $doctorId)->where('status', 'booked')->count())
                    ->icon('heroicon-s-calendar'),

                'Completed' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('doctor_id', $doctorId)->where('status', 'completed'))
                    ->badge(Appointment::where('doctor_id', $doctorId)->where('status', 'completed')->count())
                    ->icon('heroicon-s-check-circle'),

                'Missed' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('doctor_id', $doctorId)->where('status', 'missed'))
                    ->badge(Appointment::where('doctor_id', $doctorId)->where('status', 'missed')->count())
                    ->icon('heroicon-s-x-circle'),
            ];
        } elseif ($user->role == 'patient') {
            // Get the patient's ID based on the user
            $patientId = $user->patient->id;

            // Define tabs for patient
            $tabs = [
                'All' => Tab::make()
                    ->badge(Appointment::where('patient_id', $patientId)->count())
                    ->icon('heroicon-s-ellipsis-horizontal-circle'),

                'Pending' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('patient_id', $patientId)->where('status', 'pending'))
                    ->badge(Appointment::where('patient_id', $patientId)->where('status', 'pending')->count())
                    ->icon('heroicon-s-clock'),

                'Booked' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('patient_id', $patientId)->where('status', 'booked'))
                    ->badge(Appointment::where('patient_id', $patientId)->where('status', 'booked')->count())
                    ->icon('heroicon-s-calendar'),

                'Completed' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('patient_id', $patientId)->where('status', 'completed'))
                    ->badge(Appointment::where('patient_id', $patientId)->where('status', 'completed')->count())
                    ->icon('heroicon-s-check-circle'),

                'Missed' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('patient_id', $patientId)->where('status', 'missed'))
                    ->badge(Appointment::where('patient_id', $patientId)->where('status', 'missed')->count())
                    ->icon('heroicon-s-x-circle'),


            ];
        } elseif ($user->role == 'admin') {
            // Admin can see all appointments
            $tabs = [
                'All' => Tab::make()
                    ->badge(Appointment::count())
                    ->icon('heroicon-s-ellipsis-horizontal-circle'),

                'Pending' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                    ->badge(Appointment::where('status', 'pending')->count())
                    ->icon('heroicon-s-clock'),

                'Booked' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'booked'))
                    ->badge(Appointment::where('status', 'booked')->count())
                    ->icon('heroicon-s-calendar'),

                'Completed' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'completed'))
                    ->badge(Appointment::where('status', 'completed')->count())
                    ->icon('heroicon-s-check-circle'),



                'Missed' => Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'missed'))
                    ->badge(Appointment::where('status', 'missed')->count())
                    ->icon('heroicon-s-x-circle'),
            ];
        }

        return $tabs;
    }
}
