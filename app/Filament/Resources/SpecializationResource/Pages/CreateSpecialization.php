<?php

namespace App\Filament\Resources\SpecializationResource\Pages;

use App\Filament\Resources\SpecializationResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSpecialization extends CreateRecord
{
    protected static string $resource = SpecializationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): Notification
{
    $name = $this->record->name;
    return Notification::make()
    ->success()
    ->icon('heroicon-o-plus-circle')
    ->title('Specialization created')
    ->body("$name specialization has been created.");
}
}
