<?php

namespace App\Filament\Resources\SparepartSaleResource\Pages;

use App\Filament\Resources\SparepartSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSparepartSales extends ListRecords
{
    protected static string $resource = SparepartSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
