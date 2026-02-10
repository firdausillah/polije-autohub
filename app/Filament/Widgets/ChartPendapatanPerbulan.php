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
        $month  = Carbon::createFromFormat('Y-m', $filter);

        $startDate = $month->copy()->startOfMonth()->format('Y-m-d');
        $endDate   = $month->copy()->endOfMonth()->format('Y-m-d');

        $rows = DB::table('jurnals')
        ->select(
            DB::raw('CEIL(DAY(jurnals.tanggal_transaksi) / 7) as week_of_month'),
            DB::raw('SUM(CASE WHEN jurnals.debit = 0 THEN jurnals.kredit ELSE jurnals.debit END) as jumlah')
        )
            ->leftJoin('accounts', 'jurnals.account_id', '=', 'accounts.id')
            ->whereIn('accounts.type', ['Pendapatan', 'Pendapatan Lain-lain'])
            ->whereBetween('jurnals.tanggal_transaksi', [$startDate, $endDate])
            ->groupBy(DB::raw('CEIL(DAY(jurnals.tanggal_transaksi) / 7)'))
            ->orderBy(DB::raw('CEIL(DAY(jurnals.tanggal_transaksi) / 7)'))
            ->get();

        // mapping hasil query → week => jumlah
        $data = $rows->pluck('jumlah', 'week_of_month');

        // jumlah minggu dalam bulan (4 atau 5)
        $totalWeeks = ceil($month->daysInMonth / 7);

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan Mingguan ' . $month->translatedFormat('F Y'),
                    'data' => collect(range(1, $totalWeeks))
                        ->map(fn ($week) => (float) ($data[$week] ?? 0))
                        ->toArray(),
                ],
            ],
            'labels' => collect(range(1, $totalWeeks))
                ->map(fn ($week) => 'Minggu ' . $week)
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
