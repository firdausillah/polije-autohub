<?php

namespace App\Filament\Resources\ServiceScheduleReportResource\Pages;

use App\Filament\Resources\ServiceScheduleReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceScheduleReports extends ListRecords
{
    protected static string $resource = ServiceScheduleReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
