<?php

// namespace App\Filament\Resources\KartuStokResource\Pages;

// use App\Filament\Resources\KartuStokResource;
// use Filament\Actions;
// use Filament\Resources\Pages\ListRecords;

// class ListKartuStoks extends ListRecords
// {
//     protected static string $resource = KartuStokResource::class;

//     protected function getHeaderActions(): array
//     {
//         return [
//             Actions\CreateAction::make(),
//         ];
//     }
// }


namespace App\Filament\Resources\KartuStokResource\Pages;

use App\Filament\Resources\KartuStokResource;
use Filament\Resources\Pages\ListRecords;

class ListKartuStoks extends ListRecords
{
    protected static string $resource = KartuStokResource::class;
}

