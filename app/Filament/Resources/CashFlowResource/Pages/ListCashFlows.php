<?php

namespace App\Filament\Resources\CashFlowResource\Pages;

use App\Filament\Resources\CashFlowResource;
use App\Helpers\UpdatePart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashFlows extends ListRecords
{
    protected static string $resource = CashFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('update service')
                ->action(function () {
                    UpdatePart::Service();

                }),
            Actions\Action::make('update sales')
                ->action(function () {
                    UpdatePart::Sale();

                })
        ];
    }
}
