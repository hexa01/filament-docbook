<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    ->options(function (callable $get){

                        $messageService = app(MessageService::class);
                        $appointments = $messageService->getCompletedAppointments();

                        $appointments->mapWithKeys(function ($appointment) {
                                return [
                                    $appointment->id => "{$appointment->patient->user->name} with Dr. {$appointment->doctor->user->name} on {$appointment->appointment_date}",
                                ];
                            });
                            
                        }
                    )
                    ->searchable()
                    ->required()
                    ->placeholder('Select a completed appointment')
                    ->visible(fn() => !request()->query('appointment_id')),

                Forms\Components\Hidden::make('appointment_id')
                    ->default(fn() => request()->query('appointment_id'))
                    ->visible(fn() => request()->query('appointment_id')),

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
                Tables\Columns\TextColumn::make('appointment_id')
                    ->label('Appointment Details')
                    ->getStateUsing(fn($record) =>
                    // $record->appointment_id . ' - ' .
                    $record->appointment->patient->user->name . ' with ' .
                        $record->appointment->doctor->user->name . ' on ' .
                        $record->appointment->appointment_date)
                    ->sortable(),
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
                    $appointments = Appointment::where('doctor_id',$user->doctor->id)->get();
                    if ($appointments->isNotEmpty()) {
                        $appointmentIds = $appointments->pluck('id');
                    return $query->whereIn('appointment_id', $appointmentIds);
                }
            }

                // If the user is a patient, only their received messages are shown
                if ($user->hasRole('patient')) {
                    $appointments = Appointment::where('patient_id',$user->patient->id)->get();
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
                Tables\Actions\EditAction::make(),
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
            'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
}
