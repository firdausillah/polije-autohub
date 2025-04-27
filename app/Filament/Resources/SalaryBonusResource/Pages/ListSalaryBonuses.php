<?php

namespace App\Filament\Resources\SalaryBonusResource\Pages;

use App\Filament\Resources\SalaryBonusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalaryBonuses extends ListRecords
{
    protected static string $resource = SalaryBonusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
