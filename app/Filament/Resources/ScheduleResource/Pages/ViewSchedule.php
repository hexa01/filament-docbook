<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSchedule extends ViewRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
            Actions\Action::make('Index')
            ->label('View all Schedules')
            ->url(route('filament.admin.resources.schedules.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Schedule Information')
                    ->schema([
                        TextEntry::make('doctor.user.name')->label("Doctor's name"),
                        TextEntry::make('doctor.specialization.name')->label("Doctor's Specialization"),
                        TextEntry::make('day')->label('Day'),
                        TextEntry::make('start_time')->label('Start Time'),
                        TextEntry::make('end_time')->label('End Time'),
                    ])->columns(2)


            ]);
    }
}
