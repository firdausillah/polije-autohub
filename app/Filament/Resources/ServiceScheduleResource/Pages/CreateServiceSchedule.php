<?php

namespace App\Filament\Resources\ServiceScheduleResource\Pages;

use App\Filament\Resources\ServiceScheduleResource;
use App\Helpers\CodeGenerator;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceSchedule extends CreateRecord
{
    protected static string $resource = ServiceScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['kode'] = CodeGenerator::generateTransactionCode(
            'SSJ',
            'service_schedules',
            'kode'
        );

        return $data;
    }
}