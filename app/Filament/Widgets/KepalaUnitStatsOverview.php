<?php

namespace App\Filament\Widgets;

use App\Models\IncomeOverviews;
use App\Models\LabaRugi;
use App\Models\SalesReport;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KepalaUnitStatsOverview extends BaseWidget
{
    protected static ?int $sort = 30;

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

        // Base waktu tetap
        // $now = Carbon::now();

        $pendapatan_total = optional(IncomeOverviews::where(['id' => Auth::id()])->first())??0;
        // dd($pendapatan_total);

        // $description = 

        $naik_turun_service = ($pendapatan_total->service_perbandingan_persen >= 100 ? 'Naik ' : 'Turun ');
        $naik_turun_sparepart = ($pendapatan_total->part_perbandingan_persen >= 100 ? 'Naik ' : 'Turun ');
        $naik_turun_liquid = ($pendapatan_total->liquid_perbandingan_persen >= 100 ? 'Naik ' : 'Turun ');

        return [
            Stat::make('Pendapatan Service', $pendapatan_total->service_perbandingan_persen.'%')
            ->description(
                    $naik_turun_service. ($pendapatan_total->service_perbandingan_persen - 100) .'%'
                )
            ->descriptionIcon($pendapatan_total->service_perbandingan_persen >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($pendapatan_total->service_perbandingan_persen >= 100 ? 'success' : 'danger'),
            // ->chart($labaChart),

            Stat::make('Pendapatan Sparepart', $pendapatan_total->part_perbandingan_persen . '%')
            ->description(
                    $naik_turun_sparepart. ($pendapatan_total->part_perbandingan_persen - 100) .'%'
                )
            ->descriptionIcon($pendapatan_total->part_perbandingan_persen >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($pendapatan_total->part_perbandingan_persen >= 100 ? 'success' : 'danger'),
            // ->chart($itemChart),

            Stat::make('Pendapatan Liquid', $pendapatan_total->liquid_perbandingan_persen . '%')
            ->description(
                    $naik_turun_liquid. ($pendapatan_total->liquid_perbandingan_persen - 100) .'%'
                )
            ->descriptionIcon($pendapatan_total->liquid_perbandingan_persen >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($pendapatan_total->liquid_perbandingan_persen >= 100 ? 'success' : 'danger'),
                // ->chart($serviceChart),
        ];
    }
}
