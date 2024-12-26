<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
            Actions\Action::make('Index')
            ->label('View all Appointments')
            ->url(route('filament.admin.resources.appointments.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {

        return $infolist
            ->schema([
                Section::make('Basic Appointment Information')
                    ->schema([
                        TextEntry::make('patient.user.name')->label('Patient Name'),
                        TextEntry::make('doctor.user.name')->label('Doctor Name'),
                        TextEntry::make('appointment_date')->label('Appointment Date'),
                        TextEntry::make('slot')->label('Appointment Slot Time'),
                        TextEntry::make('status')->label('Appointment Status'),
                        // TextEntry::make('doctors_count')->label('Number of doctors for this specialization')
                    ])->columns(2),
                Section::make('Payment Information')
                    ->schema([
                        TextEntry::make('payment.status')->label('Payment Status'),
                        TextEntry::make('payment.payment_method')->label('Payment Method'),
                        // TextEntry::make('doctors_count')->label('Number of doctors for this specialization')
                    ])->columns(2)


            ]);
    }
}
