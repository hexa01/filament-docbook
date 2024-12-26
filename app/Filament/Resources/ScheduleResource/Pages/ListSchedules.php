<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

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
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor Name')
                    ->searchable()
                    ->sortable()
                    ->hidden(fn() => Auth::user()->role === 'doctor'),
                Tables\Columns\TextColumn::make('day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
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

                // If the user is an admin, they can see all schedules
                if ($user->hasRole('admin')) {
                    return $query;
                }

                // If the user is a doctor, only their schedules are shown
                if ($user->hasRole('doctor')) {
                    return $query->where('doctor_id', $user->doctor->id);
                }

                // If the user is a patient, only their selected doctors schedule are shown
                // if ($user->hasRole('patient')) {
                //     return $query->where('patient_id', $user->patient->id);
                // }

                //default
                return $query->whereRaw('1 = 0');
            })
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('View Schedule')
                        ->color('viewButton'),
                    Action::make('updateSchedule')
                        ->label("Edit Schedule")
                        ->form([
                            Forms\Components\TextInput::make('day')
                                ->label('Day')
                                ->default(fn($record) => $record->day)
                                ->required()
                                ->disabled()
                                ->readonly(),
                            Forms\Components\TimePicker::make('start_time')
                                ->label('Select Start Time')
                                ->displayFormat('H:i')
                                ->seconds(false)
                                ->default(fn($record) => $record->start_time)
                                ->required(),
                            Forms\Components\TimePicker::make('end_time')
                                ->label('Select End Time')
                                ->required()
                                ->seconds(false)
                                ->default(fn($record) => $record->end_time)
                                ->after('start_time')
                                ->rule('after:start_time')
                                ->rule(function (callable $get) {
                                    return function ($attribute, $value, $fail) use ($get) {
                                        $startTime = $get('start_time');
                                        if (strtotime($value) < strtotime('+2 hours', strtotime($startTime))) {
                                            $fail('End time must be at least 2 hours after the start time.');
                                        }
                                    };
                                }),
                        ])
                        ->color('yellow')
                        ->icon('heroicon-m-pencil-square')
                        ->action(function ($record, $data) {
                            $startTime = Carbon::parse($data['start_time']);
                            $endTime = Carbon::parse($data['end_time']);

                            $appointment_dates = Appointment::where('doctor_id', $record->doctor_id)->whereDate('appointment_date', '>', now())->distinct()->pluck('appointment_date')->toArray();
                            $appointment_days = array_map(function ($date) {
                                return Carbon::parse($date)->englishDayOfWeek;
                            }, $appointment_dates);
                            if (in_array($record->day, $appointment_days)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Schedule not updated')
                                    ->body('You can\'t update schedule for this day.')
                                    ->send();
                                return redirect()->back();
                            }


                            // Calculate the number of 30-minute slots
                            $slot_count = $startTime->diffInMinutes($endTime) / 30;
                            $record->update([
                                'start_time' => $startTime->format('H:i'),  // 24-hour format
                                'end_time' => $endTime->format('H:i'),      // 24-hour format
                                'slot_count' => $slot_count,
                            ]);

                            // $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
                            Notification::make()
                                ->title('Schedule updated')
                                ->success()
                                ->body("Schedule updated for $record->day")
                                ->send();
                        })
                ])
                    ->tooltip('More Actions')
                    ->label('Actions')
                    ->icon('heroicon-s-cog')
                    ->size(ActionSize::Small)
                    ->color('action')
                    ->button()
            ])

            ->bulkActions([
                // Bulk Action for updating the status of selected records
                BulkAction::make('updateScheduleBulk')
                    ->label('Update Schedules for Selected Days')
                    ->form([
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Select Start Time')
                            ->seconds(false)
                            ->default('10:00')
                            ->required(),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Select End Time')
                            ->required()
                            ->after('start_time')
                            ->default('17:00')
                            // ->rule('after:start_time')
                            ->seconds(false)
                            ->rule(function (callable $get) {
                                return function ($attribute, $value, $fail) use ($get) {
                                    $startTime = $get('start_time');
                                    if (strtotime($value) < strtotime('+2 hours', strtotime($startTime))) {
                                        $fail('End time must be at least 2 hours after the start time.');
                                    }
                                };
                            }),
                    ])->action(function ($records, $data) {

                        // Update the status for selected records
                        foreach ($records as $record) {
                            $appointment_dates = Appointment::where('doctor_id', $record->doctor_id)->whereDate('appointment_date', '>', now())->distinct()->pluck('appointment_date')->toArray();
                            $appointment_days = array_map(function ($date) {
                                return Carbon::parse($date)->englishDayOfWeek;
                            }, $appointment_dates);
                            if (in_array($record->day, $appointment_days)) {
                                $doctor_name = $record->doctor->user->name;
                                $text = "You can't update schedule on $record->day as it overlaps with existing appointment.";
                                if (Auth::user()->role === 'admin') {
                                    $text = "You can't update schedule for $doctor_name on $record->day as it overlaps with existing appointment.";
                                }
                                Notification::make()
                                    ->danger()
                                    ->title('Schedule not updated')
                                    ->body($text)
                                    ->send();
                                return redirect()->back();
                            }

                            $startTime = Carbon::parse($data['start_time']);
                            $endTime = Carbon::parse($data['end_time']);

                            // Calculate the number of 30-minute slots
                            $slot_count = $startTime->diffInMinutes($endTime) / 30;
                            $record->update([
                                'start_time' => $startTime->format('H:i'),  // 24-hour format
                                'end_time' => $endTime->format('H:i'),      // 24-hour format
                                'slot_count' => $slot_count,
                            ]);
                        }
                        Notification::make()
                            ->title('Schedules Updated for selected days.')
                            ->success()
                            ->send();
                    }),
            ]);
    }


}
