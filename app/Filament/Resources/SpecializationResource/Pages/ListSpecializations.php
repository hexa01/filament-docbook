<?php

namespace App\Filament\Resources\SpecializationResource\Pages;

use App\Filament\Resources\SpecializationResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;

class ListSpecializations extends ListRecords
{
    protected static string $resource = SpecializationResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        $user = User::find(Auth::user()->id);
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctors_count')->counts('doctors')
                    ->label('Number of Doctors')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Edit Specialization')
                        ->color('yellow'),
                    DeleteAction::make()
                        ->label('Delete Specialization')
                        ->before(function ($record, DeleteAction $action) {
                            if ($record->doctors()->exists()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Specialization not deleted')
                                    ->body('Please manage the doctors registered in this specialization first.')
                                    ->send();
                                $action->cancel();
                            }
                        })
                        ->successNotification(function ($record) {
                            return Notification::make()
                                ->danger()
                                ->icon('heroicon-o-trash')
                                ->title('Specialization Removed!')
                                ->body("$record->name specialization has been deleted.");
                        }),
                ])
                    ->tooltip('More Actions')
                    ->label('Actions')
                    ->hidden($user->role != 'admin')
                    ->icon('heroicon-s-cog')
                    ->size(ActionSize::Small)
                    ->color('action')
                    ->button(),
            ])
        ;
    }

}
