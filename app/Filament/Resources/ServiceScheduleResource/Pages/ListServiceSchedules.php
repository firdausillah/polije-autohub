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

    function generateTab(string $status, string $color): Tab
    {
        return Tab::make()
        ->badgeColor($color)
        ->badge(
            ServiceSchedule::query()
                ->where('service_status', $status)
                ->when(
                    !in_array($status, ['Daftar', 'Selesai']) && auth()->user()->hasRole('Mekanik'),
                    fn ($query) => $query->where('mekanik_id', auth()->id()) // Filter hanya untuk mekanik
                )
                ->when(
                    !in_array($status, ['Daftar', 'Selesai']) && auth()->user()->hasRole('Kepala Mekanik'),
                    fn ($query) => $query->where('kepala_mekanik_id', auth()->id()) // Filter hanya untuk kepala mekanik
                )
                ->whereDate('created_at', now()->toDateString())
                ->count()
        )
        ->modifyQueryUsing(
            fn (Builder $query) => $query->where('service_status', $status)
        );



    }

    public function getTabs(): array
    {
        return [
            'daftar' => $this->generateTab('Daftar', 'info'),
            'proses_pengerjaan' => $this->generateTab('Proses Pengerjaan', 'warning'),
            'batal' => $this->generateTab('Batal', 'danger'),
            'pembayaran' => $this->generateTab('Menunggu Pembayaran', 'warning'),
            'selesai' => $this->generateTab('Selesai', 'success'),
            'semua' => Tab::make()
                ->badge(
                    ServiceSchedule::query()
                        ->when(
                            auth()->user()->hasRole('Mekanik'),
                            fn ($query) =>
                            $query->where('mekanik_id', auth()->id())
                        )
                        ->whereDate('created_at', now()->toDateString())
                        ->count()
                ),
        ];
    }
}
