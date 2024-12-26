<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecializationResource\Pages;
use App\Filament\Resources\SpecializationResource\RelationManagers\DoctorsRelationManager;
use App\Models\Specialization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

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
