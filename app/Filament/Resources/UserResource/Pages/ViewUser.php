<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
            ->label("Edit this user"),
            Actions\Action::make('Index')
            ->label('View all Users')
            ->url(route('filament.admin.resources.users.index')),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make()
                ->schema([
                    Section::make("User's Basic Information")
                        ->description('Basic details about the user.')
                        ->schema([
                            TextEntry::make('name')->label('Full Name'),
                            TextEntry::make('dob')->label('Date of Birth'),
                            TextEntry::make('gender')->label('Gender'),
                            // TextEntry::make('role')->label('User Role'),
                        ])->columns(3),

                    Section::make('Contact Information')
                        ->description('Reach out to the user via the details below.')
                        ->schema([
                            TextEntry::make('email')->label('Email Address'),
                            TextEntry::make('phone')->label('Phone Number'),
                        ])->columns(2), // Stack fields vertically in this section
                ])->columns(2),

            Section::make('Professional Details')
                ->description('Relevant information for doctors.')
                ->schema([
                    TextEntry::make('doctor.specialization.name')
                        ->label('Specialization')
                        // ->visible(fn($record) => $record->role === 'doctor')
                        ,
                    TextEntry::make('doctor.hourly_rate')
                        ->label('Hourly Rate ($)')
                        // ->visible(fn($record) => $record->role === 'doctor')
                        ,
                ])
                ->visible(fn($record) => $record->role === 'doctor')
                ->columns(2),
        ]);
    }
}
