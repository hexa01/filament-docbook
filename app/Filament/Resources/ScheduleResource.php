<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use phpDocumentor\Reflection\Types\Callable_;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('doctor_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('day')
                    ->required(),
                Forms\Components\TextInput::make('start_time')
                    ->required(),
                Forms\Components\TextInput::make('end_time')
                    ->required(),
                Forms\Components\TextInput::make('slots')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('day')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time'),
                Tables\Columns\TextColumn::make('end_time'),
                Tables\Columns\TextColumn::make('slots')
                    ->numeric()
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
            ])
            ->bulkActions([
 // Bulk Action for updating the status of selected records
                BulkAction::make('updateScheduleBulk')
                ->label('Update Schedules for Selected Days')
                ->form([
                    Forms\Components\TimePicker::make('start_time')
                        ->label('Select Start Time')
                        ->displayFormat('H:i')
                        ->required(),

                    Forms\Components\TimePicker::make('end_time')
                        ->label('Select End Time')
                        ->required()
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
                    ])->action(function ($records, $data) {
                        // Update the status for selected records
                        foreach ($records as $record) {
                            $startTime = Carbon::parse($data['start_time']);
                            $endTime = Carbon::parse($data['end_time']);

                            // Calculate the number of 30-minute slots
                            $slots = $startTime->diffInMinutes($endTime) / 30;

                            $record->update([
                                'start_time' => $startTime->format('H:i'),  // 24-hour format
                                'end_time' => $endTime->format('H:i'),      // 24-hour format
                                'slots' => $slots,
                            ]);
                        }

                        Notification::make()
                            ->title('Selected Schedules Updated')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'view' => Pages\ViewSchedule::route('/{record}'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
