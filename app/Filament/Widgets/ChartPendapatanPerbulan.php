<?php

namespace App\Filament\Widgets;

use App\Models\LabaRugi;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;


class ChartPendapatanPerbulan extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan Perbulan';

    protected static ?int $sort = 50;

    // protected int | string | array $columnSpan = 'full';

    // public static function canView(): bool
    // {
    //     return auth()->check() && auth()->user()->hasRole(['Kepala Unit']);
    // }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    public ?string $filter = null;

    public function mount(): void
    {
        parent::mount();

        $this->filter = now()->format('Y-m');
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? now()->format('Y-m');

        $month = Carbon::createFromFormat('Y-m', $filter);

        $startDate = $month->copy()->startOfMonth();
        $endDate   = $month->copy()->endOfMonth();

        $tanggalArray = collect(range(1, $endDate->day))
            ->map(fn ($day) => $startDate->copy()->day($day)->format('Y-m-d'));

        $labaChart = $tanggalArray->map(function ($date) {
            return (float) (
                LabaRugi::getTotalPendapatan(
                    $date . ' 00:00:00',
                    $date . ' 23:59:59'
                )[0]->jumlah ?? 0
            );
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pendapatan ',
                    'data'  => $labaChart,
                ],
            ],
            'labels' => $tanggalArray
                ->map(fn ($d) => Carbon::parse($d)->day)
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }


    protected function getFilters(): ?array
    {
        $start = now()->startOfMonth();               // bulan sekarang
        $end   = Carbon::create(2025, 6, 1);           // FIX END (terlama)

        $filters = [];

        while ($start >= $end) {
            $value = $start->format('Y-m');            // 2025-09
            $label = $start->translatedFormat('F Y');  // September 2025

            $filters[$value] = $label;

            $start->subMonth();
        }

        return $filters;
    }
    

}
