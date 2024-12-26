<?php

namespace App\Filament\Resources\SpecializationResource\Pages;

use App\Filament\Resources\SpecializationResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSpecialization extends ViewRecord
{
    protected static string $resource = SpecializationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
            Actions\Action::make('Index')
            ->label('View all Specializations')
            ->url(route('filament.admin.resources.specializations.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Specialization Information')
                    ->description('Information about the specialization')
                    ->schema([
                        TextEntry::make('name')->label('Specialization name'),
                        TextEntry::make('doctors')
                            ->formatStateUsing(fn($record) => $record->doctors()->count())
                            ->label('Number of doctors for this specialization')
                    ])->columns(2)


            ]);
    }

}
