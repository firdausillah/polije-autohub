<?php

namespace App\Filament\Resources\SparepartPurchaseResource\Pages;

use App\Filament\Resources\SparepartPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSparepartPurchases extends ListRecords
{
    protected static string $resource = SparepartPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
