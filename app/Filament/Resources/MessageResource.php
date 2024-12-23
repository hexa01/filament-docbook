<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\MessageService;

use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Appointment Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('appointment_id')
                    ->label('Select Completed Appointment')
                    ->options(
                        function () {
                            $messageService = app(MessageService::class);
                            $appointments = $messageService->getCompletedAppointments();
                            return app(AppointmentService::class)->formatAppointmentsAsReadableText($appointments);
                            // return $AppointmentService->formatAppointmentsAsReadableText($appointments);
                        }
                    )
                    ->searchable()
                    ->required()
                    ->placeholder('Select a completed appointment')
                    ->hidden(fn() => request()->query('appointment_id')),

                Forms\Components\Hidden::make('appointment_id')
                    ->default(fn() => request()->query('appointment_id'))
                    ->hidden(fn() => !request()->query('appointment_id')),

                Forms\Components\Textarea::make('doctor_message')
                    ->label("Doctor's Message")
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    // public static function getActions(): array
    // {
    //     return [
    //         Action::make('create')
    //             ->label('Add New Review')
    //             ->hidden(fn () => !request()->query('appointment_id'))
    //             ->url(fn () => route('filament.admin.resources.reviews.create')),
    //     ];
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appointment.doctor.user.name')
                ->hidden()
                ->searchable(),
                Tables\Columns\TextColumn::make('appointment_id')
                    ->label('Appointment Details')
                    ->getStateUsing(fn($record) =>
                    // $record->appointment_id . ' - ' .
                    $record->appointment->patient->user->name . ' with ' .
                        $record->appointment->doctor->user->name . ' on ' .
                        $record->appointment->appointment_date)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('doctor_message')
                    ->label("Doctor's Message"),
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
                Tables\Actions\ViewAction::make(),
                Action::make('updateMessage')
                ->hidden(fn()=>Auth::user()->role === 'patient')
                    ->label("Edit")
                    ->form([
                        TextInput::make('doctor_message')
                            ->default(fn($record) => $record->doctor_message)
                            ->label("Doctor's Message")
                            ->required()
                    ])
                    ->color('yellow')
                    ->icon('heroicon-s-pencil')
                    ->action(function ($record, $data) {
                        $record->update([
                            'doctor_message' => $data['doctor_message'],
                        ]);
                        // $text = app(AppointmentService::class)->formatAppointmentAsReadableText($record);
                        Notification::make()
                            ->title('Message updated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
            'view' => Pages\ViewMessage::route('/{record}'),
            // 'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
}
