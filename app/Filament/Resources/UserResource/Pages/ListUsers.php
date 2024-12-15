<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make(),
            'Doctors' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'doctor'))
                ->badge(User::query()->where('role', 'doctor')->count()),
            'Patients' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'patient'))
                ->badge(User::query()->where('role', 'patient')->count()),
            'Admins' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'admin'))
                ->badge(User::query()->where('role', 'admin')->count()),  // Ensure 'admin' is correct
        ];
    }


}
