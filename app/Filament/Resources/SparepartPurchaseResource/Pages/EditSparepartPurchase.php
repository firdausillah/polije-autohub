<?php

namespace App\Filament\Resources\SparepartPurchaseResource\Pages;

use App\Filament\Resources\SparepartPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSparepartPurchase extends EditRecord
{
    protected static string $resource = SparepartPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
