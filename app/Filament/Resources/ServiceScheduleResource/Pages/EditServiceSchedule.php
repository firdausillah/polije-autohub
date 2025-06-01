<?php

namespace App\Filament\Resources\ServiceScheduleResource\Pages;

use App\Filament\Resources\ServiceScheduleResource;
use App\Helpers\updateServiceTotal;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditServiceSchedule extends EditRecord
{
    protected static string $resource = ServiceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return
        [
            // Bawaan Filament
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),

            // Custom Tombol
            Action::make('generate_total')
            ->label('Generate Total')
            ->color('info')
            ->icon('heroicon-m-calculator')
            ->action(function () {
                $record = $this->getRecord();

                updateServiceTotal::updateTotal($record->id);

                \Filament\Notifications\Notification::make()
                    ->title('Sukses')
                    ->body('Total berhasil digenerate!')
                    ->success()
                    ->send();
            })
            ->visible(function(){
                $record = $this->getRecord();
                return $record->service_status == 'Proses Pengerjaan' OR $record->service_status == 'Menunggu Pembayaran';
            }),
        ];
    }
}
