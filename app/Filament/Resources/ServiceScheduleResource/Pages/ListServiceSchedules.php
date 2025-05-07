<?php

namespace App\Filament\Resources\ServiceScheduleResource\Pages;

use App\Filament\Resources\ServiceScheduleResource;
use App\Models\ServiceSchedule;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class ListServiceSchedules extends ListRecords
{
    protected static string $resource = ServiceScheduleResource::class;

    public SupportCollection $serviceScheduleByStatuses;

    public function __construct()
    {
        $this->serviceScheduleByStatuses = ServiceSchedule::select('service_status', DB::raw('count(*) as status_count'))
            ->whereDate('created_at', now()->toDateString())
            ->groupBy('service_status')
            ->pluck('status_count', 'service_status');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function baseQueryWithRoleFilter(): Builder
    {
        $query = ServiceSchedule::query()->whereDate('created_at', now()->toDateString());

        if (auth()->user()->hasRole('Mekanik')) {
            $query->where(function ($query) {
                $query->where('mekanik1_id', auth()->id())
                    ->orWhere('mekanik2_id', auth()->id())
                    ->orWhere('mekanik3_id', auth()->id());
            });
        }

        if (auth()->user()->hasRole('Kepala Unit')) {
            $query->where('kepala_mekanik_id', auth()->id());
        }
        // dd($query->get());
        return $query;
    }

    protected function generateTab(string $status, string $color): Tab
    {
        $query = ServiceSchedule::query()
            ->where('service_status', $status)
            ->whereDate('created_at', now()->toDateString());
        if (!in_array($status, ['Daftar', 'Selesai'])) {
            if (auth()->user()->hasRole('Mekanik')) {
                $query->where(function ($query) {
                    $query->where('mekanik1_id', auth()->id())
                        ->orWhere('mekanik2_id', auth()->id())
                        ->orWhere('mekanik3_id', auth()->id());
                });
            }

            if (auth()->user()->hasRole('Kepala Unit')) {
                $query->where('kepala_mekanik_id', auth()->id());
            }
        }

        return Tab::make()
            ->badgeColor($color)
            ->badge($query->count())
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
                ->badge($this->baseQueryWithRoleFilter()->count()),
        ];
    }
}
