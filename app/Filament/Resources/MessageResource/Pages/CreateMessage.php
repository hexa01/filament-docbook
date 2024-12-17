<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use App\Models\Message;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // Check if a review already exists for the selected appointment
    //     $existingMessage = Message::where('appointment_id', $data['appointment_id'])->first();

    //     if ($existingMessage) {
    //         // Send error notification
    //         Notification::make()
    //             ->title('Error')
    //             ->body('A message already exists for this appointment.')
    //             ->danger()
    //             ->send();

    //         // Throw a validation exception to stop the form submission
    //         throw ValidationException::withMessages([
    //             'appointment_id' => 'A message already exists for this appointment.',
    //         ]);
    //     }

    //     return $data;
    // }


}
