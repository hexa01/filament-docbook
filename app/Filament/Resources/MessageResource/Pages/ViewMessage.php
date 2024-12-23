<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewMessage extends ViewRecord
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
            Actions\Action::make('Index')
            ->label('View all Messages')
            ->url(route('filament.admin.resources.messages.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Appointment Information')
                    ->schema([
                        TextEntry::make('appointment.patient.user.name')->label("Patient's name"),
                        TextEntry::make('appointment.doctor.user.name')->label("Doctor's name"),
                        TextEntry::make('appointment.appointment_date')->label("Appointment Date"),
                        TextEntry::make('appointment.slot')->label('Appointment Time'),
                    ])->columns(2),
                Section::make('Message Information')
                    ->schema([
                TextEntry::make('doctor_message')->label('Doctor\'s Message'),
                    ])->columns(1)


            ]);
    }


}
