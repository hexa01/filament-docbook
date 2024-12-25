<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label("Update General Information"),
            $this->getCancelFormAction(),
        ];
    }
    protected function getForms(): array
    {
        // dd($this->Action);
        $this->maxWidth = '6xl';
        // dd($this->getSaveFormAction());
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                          Split::make([
                            Section::make('General Information')
                            ->schema([
                                $this->getNameFormComponent(),
                                $this->getEmailFormComponent(),
                                DatePicker::make('dob')
                                    ->label('Date of Birth')
                                    ->required()
                                    ->displayFormat('d/m/Y'),
                                TextInput::make('address'),
                                TextInput::make('phone'),
                            ])
                            ->collapsible()  // Optional collapsibility for better UX
                            ->columns(1),    // Ensure fields are stacked in this section

                        // Right Section: Password Management
                        Section::make('Password Management')
                            ->schema([
                                TextInput::make('old_password')
                                    ->password()
                                    ->revealable()
                                    ->label('Old Password'),
                                $this->getPasswordFormComponent(),
                                $this->getPasswordConfirmationFormComponent(),
                            ])
                            ->collapsible()  // Optional collapsibility for better UX
                            ->columns(1),
                          ])  // Left Section: General Information
                               // Ensure fields are stacked in this section
                        ])
                    ->statePath('data'),
            ),
        ];



    }


    protected function afterSave(){
        return redirect()->route('filament.admin.pages.dashboard');
    }
}
