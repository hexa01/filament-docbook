<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecializationResource\Pages;
use App\Filament\Resources\SpecializationResource\RelationManagers;
use App\Filament\Resources\SpecializationResource\RelationManagers\DoctorsRelationManager;
use App\Models\Specialization;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action as Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SpecializationResource extends Resource
{

    protected static ?string $model = Specialization::class;
    protected static ?string $navigationLabel = 'Specialization';
    protected static ?string $navigationGroup = 'Specializations';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique()
                    ->maxLength(255),

            ]);
    }

    public static function table(Table $table): Table
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
                                ->success()
                                ->icon('heroicon-o-trash')
                                ->title('Appointment Removed!')
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

    public static function infolist(Infolist $infolist): Infolist
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

    public static function getRelations(): array
    {
        return [
            DoctorsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecializations::route('/'),
            'create' => Pages\CreateSpecialization::route('/create'),
            'view' => Pages\ViewSpecialization::route('/{record}'),
            // 'edit' => Pages\EditSpecialization::route('/{record}/edit'),
        ];
    }
}
