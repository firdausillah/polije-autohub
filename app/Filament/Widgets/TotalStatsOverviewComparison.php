<?php

namespace App\Filament\Widgets;

use App\Helpers\Round;
use App\Models\IncomeOverviews;
use App\Models\LabaRugi;
use App\Models\LastMonthIncomeComparison;
use App\Models\PayrollJurnal;
use App\Models\SalesReport;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TotalStatsOverviewComparison extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getColumns(): int
    {
        return 2; // dua stat per baris (kiri & kanan)
    }

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['Kepala Unit']);
    }
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        $pendapatan_per_bulan_lalu = IncomeOverviews::where(['id' => Auth::id()])->first()->total_perbandingan_persen;


        // $startLastMonth = Carbon::now()->startOfMonth()->subMonth();
        // $endLastMonth   = Carbon::now()->startOfMonth();
        // $startThisMonth = Carbon::now()->startOfMonth();
        // $endThisMonth   = Carbon::now()->startOfMonth()->addMonth();

        $dataBulanIni = PayrollJurnal::selectRaw('user_id, SUM(nominal) as nominal')
        ->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->startOfMonth()->addMonth()
        ])
            ->where('user_id', Auth::id())
            ->groupBy('user_id')
            ->first();

        $dataBulanLalu = PayrollJurnal::selectRaw('user_id, SUM(nominal) as nominal')
        ->whereBetween('created_at', [
            now()->startOfMonth()->subMonth(),
            now()->startOfMonth()
        ])
            ->where('user_id', Auth::id())
            ->groupBy('user_id')
            ->first();

        // Mitigasi error
        $nominalBulanIni = $dataBulanIni->nominal ?? 0;
        $nominalBulanLalu = $dataBulanLalu->nominal ?? 0;

        // Cegah division by zero
        if ($nominalBulanLalu == 0) {
            $pendapatan_per_bulan = 0; // atau null, atau default lain
        } else {
            $pendapatan_per_bulan = Round((($nominalBulanIni / $nominalBulanLalu) * 100), 2);
        }

        $pendapatan_per_tanggal = IncomeOverviews::where(['id' => Auth::id()])->first()->total_perbandingan_persen;

        return [

            Stat::make('Perbandingan Per Tanggal', $pendapatan_per_tanggal . '%')
            ->description(
                ($pendapatan_per_tanggal >= 100 ? 'Naik ' : 'Turun ') . ($pendapatan_per_tanggal - 100) . '%'
            )

            ->descriptionIcon($pendapatan_per_tanggal >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($pendapatan_per_tanggal >= 100 ? 'success' : 'danger'),

            Stat::make('Perbandingan Per Bulan', $pendapatan_per_bulan . '%')
            ->description(
                ($pendapatan_per_bulan >= 100 ? 'Naik ' : 'Turun ') . ($pendapatan_per_bulan - 100) . '%'
            )

            ->descriptionIcon($pendapatan_per_bulan >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($pendapatan_per_bulan >= 100 ? 'success' : 'danger')
        ];
    }
}
