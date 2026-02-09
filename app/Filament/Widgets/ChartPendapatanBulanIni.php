<?php

namespace App\Filament\Widgets;

use App\Models\LabaRugi;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartPendapatanBulanIni extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penapatan Bulan Ini';

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


    protected function getData(): array
    {


        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $today = Carbon::now();

        $tanggalArray = collect(range(0, $today->day))->map(function ($day) use ($startDate) {
            return $startDate->copy()->addDays($day)->format('Y-m-d');
            // return $startDate->copy()->addDays($day)->format('Y-m-d');
        });
        
        $labaChart = $tanggalArray->map(function ($date) {
            return (float) LabaRugi::getTotalPendapatan($date. ' 00:00:01', $date . ' 23:59:59')[0]->jumlah ?? 0;
        })->toArray();
        // foreach ($labaChart as  $value) {
        //     $jumlah[] = $value;
        // }
        // dd($labaChart);
            
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pendapatan',
                    'data' => $labaChart,
                    // 'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => range(0, $today->day),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
