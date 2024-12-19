<?php

namespace App\Filament\Resources\SpecializationResource\Pages;

use App\Filament\Resources\SpecializationResource;
use Filament\Actions;
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

}
