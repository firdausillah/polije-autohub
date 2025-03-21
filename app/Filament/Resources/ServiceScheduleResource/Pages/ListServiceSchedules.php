<?php

namespace App\Filament\Resources\ServiceScheduleResource\Pages;

use App\Filament\Resources\ServiceScheduleResource;
use App\Models\ServiceSchedule;
use Filament\Actions;
use Filament\Notifications\Collection;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class ListServiceSchedules extends ListRecords
{

    public SupportCollection $serviceScheduleByStatuses;

    public function __construct() {
        $request = request();
        
        $this->serviceScheduleByStatuses = ServiceSchedule::select('service_status', DB::raw('count(*) as status_count'))
            ->where('created_at', 'like', (now()->toDateString()."%")) // 🔥 Filter by date
            ->groupBy('service_status')
            ->pluck('status_count', 'service_status');
            
    }
    protected static string $resource = ServiceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'daftar' => Tab::make()
            // ->badge($this->serviceScheduleByStatuses['Daftar']??0)
            ->badgeColor('info')
            ->badge(ServiceSchedule::query()->where('service_status', 'Daftar')->where('created_at', 'like', (now()->toDateString()."%"))->count())
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->where('service_status', 'Daftar')
            ),
            'proses_pengerjaan' => Tab::make()
            // ->badge($this->serviceScheduleByStatuses['Proses Pengerjaan']??0)
            ->badgeColor('warning')
            ->badge(ServiceSchedule::query()->where('service_status', 'Proses Pengerjaan')->where('created_at', 'like', (now()->toDateString()."%"))->count())
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->where('service_status', 'Proses Pengerjaan')
            ),
            'batal' => Tab::make()
            // ->badge($this->serviceScheduleByStatuses['Batal']??0)
            ->badgeColor('danger')
            ->badge(ServiceSchedule::query()->where('service_status', 'Batal')->where('created_at', 'like', (now()->toDateString()."%"))->count())
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->where('service_status', 'Batal')
            ),
            'selesai' => Tab::make()
            // ->badge($this->serviceScheduleByStatuses['Selesai']??0)
            ->badgeColor('success')
            ->badge(ServiceSchedule::query()->where('service_status', 'Selesai')->where('created_at', 'like', (now()->toDateString()."%"))->count())
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->where('service_status', 'Selesai')
            ),
            'semua' => Tab::make()
            ->badge(ServiceSchedule::query()->where('created_at', 'like', (now()->toDateString()."%"))->count())
                // ->badge($this->serviceScheduleByStatuses->sum()),
        ];
    }
}
