<?php

namespace App\Filament\Resources\SparepartSaleResource\Pages;

use App\Filament\Resources\SparepartSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSparepartSale extends EditRecord
{
    protected static string $resource = SparepartSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make()->disabled(fn () => $this->isCreateDisabled()),
        ];
    }

    private function isCreateDisabled(): bool
    {
        $latestRecord = $this->getModel()::latest()->first();

        return $latestRecord?->is_approve === 'approved';
    }
}
