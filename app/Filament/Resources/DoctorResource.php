<?php

namespace App\Filament\Resources;

use App\Models\Doctor;
use App\Models\Specialization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;
    protected static ?string $navigationLabel = 'Doctor';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    // protected static ?string $navigationGroup = 'Doctor Management';
    protected static ?int $navigationSort = 1;


    public static function shouldRegisterNavigation(): bool
{
    return false; // Hides the resource from the sidebar
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user.name')
                    ->required()
                    ->searchable(),
                    Forms\Components\Select::make('specialization_id')
                    ->label('Specialization')
                    ->options(fn () => Specialization::pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\TextInput::make('hourly_rate')
                    ->required()
                    ->numeric(),
                // Forms\Components\Textarea::make('bio')
                //     ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                ->label("Doctor's Name")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialization.name')
                ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hourly_rate')
                    ->numeric()
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
                SelectFilter::make('Specialization')
                ->relationship('specialization', 'name')
                ->searchable()
                ->preload()
                ->label('Filter by Specialization')
                ->indicator('Specialization'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            // 'index' => Pages\ListDoctors::route('/'),
            // 'create' => Pages\CreateDoctor::route('/create'),
            // 'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
