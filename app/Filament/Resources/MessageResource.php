<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Models\Message;
use App\Services\AppointmentService;
use App\Services\MessageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

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
                        }
                    )
                    ->searchable()
                    ->required()
                    ->placeholder('Select a completed appointment'),

                Forms\Components\Hidden::make('appointment_id')
                    ->default(fn() => request()->query('appointment_id'))
                    ->hidden(fn() => !request()->query('appointment_id')),

                Forms\Components\Textarea::make('doctor_message')
                    ->label("Doctor's Message")
                    ->required()
                    ->columnSpanFull(),
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
