<?php

namespace App\Filament\Resources\ServiceScheduleReportResource\Pages;

use App\Filament\Resources\ServiceScheduleReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceScheduleReport extends EditRecord
{
    protected static string $resource = ServiceScheduleReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
