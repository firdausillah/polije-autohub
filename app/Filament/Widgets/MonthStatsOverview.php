<?php

namespace App\Filament\Widgets;

use App\Models\LabaRugi;
use App\Models\SalesReport;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MonthStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;


    protected function getStats(): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $today = Carbon::now();

        $startLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // ==================== //
        //   Data Bulan Lalu    //
        // ==================== //
        $laba_kotor_bulan_lalu = LabaRugi::getTotalPendapatan($startLastMonth->toDateString(), $endLastMonth->toDateString())[0]->jumlah ?? 0;
        $item_terjual_bulan_lalu = DB::table('inventories')
        ->where('movement_type', 'OUT-SAL')
        ->whereBetween('tanggal_transaksi', [$startLastMonth, $endLastMonth])
            ->sum('jumlah_terkecil') ?? 0;
        $service_selesai_bulan_lalu = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('approved_at', [$startLastMonth, $endLastMonth])
            ->count();

        // ==================== //
        //   Data  Bulan Ini    //
        // ==================== //
        $laba_kotor_bulan = LabaRugi::getTotalPendapatan($startDate->toDateString(), $endDate->toDateString())[0]->jumlah ?? 0;
        $item_terjual_bulan = DB::table('inventories')
        ->where('movement_type', 'OUT-SAL')
        ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->sum('jumlah_terkecil') ?? 0;
        $service_selesai_bulan = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('approved_at', [$startDate, $endDate])
            ->count();

        // ==================== //
        //     Hitung Growth    //
        // ==================== //
        $growthLabaBulan = $laba_kotor_bulan - $laba_kotor_bulan_lalu;
        $growthItemBulan = $item_terjual_bulan - $item_terjual_bulan_lalu;
        $growthServiceBulan = $service_selesai_bulan - $service_selesai_bulan_lalu;

        // ==================== //
        //     Chart Harian     //
        // ==================== //

        $tanggalArray = collect(range(0, $today->day))->map(function ($day) use ($startDate) {
            return $startDate->copy()->addDays($day)->format('Y-m-d');
        });

        $labaChart = $tanggalArray->map(function ($date) {
            return (float) LabaRugi::getTotalPendapatan($date, $date . ' 23:59:59')[0]->jumlah ?? 0;
        })->toArray();
        // dd($labaChart);

        $itemChart = $tanggalArray->map(function ($date) {
            return (float) DB::table('inventories')
            ->where('movement_type', 'OUT-SAL')
            ->whereDate('tanggal_transaksi', $date)
                ->sum('jumlah_terkecil');
        })->toArray();

        $serviceChart = $tanggalArray->map(function ($date) {
            return (float) DB::table('service_schedules')
            ->where('is_approve', 'approved')
            ->whereDate('approved_at', $date)
                ->count();
        })->toArray();

        return [
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($laba_kotor_bulan, 0, ',', '.'))
            ->description('Perubahan: Rp ' . number_format($growthLabaBulan, 0, ',', '.'))
            ->descriptionIcon($growthLabaBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($growthLabaBulan >= 0 ? 'success' : 'danger')
            ->chart($labaChart),

            Stat::make('Item Terjual Bulan Ini', number_format($item_terjual_bulan, 0, ',', '.'))
            ->description('Perubahan: ' . number_format($growthItemBulan, 0, ',', '.') . ' item')
            ->descriptionIcon($growthItemBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($growthItemBulan >= 0 ? 'success' : 'danger')
            ->chart($itemChart),

            Stat::make('Service Selesai Bulan Ini', $service_selesai_bulan)
                ->description('Perubahan: ' . number_format($growthServiceBulan, 0, ',', '.') . ' service')
                ->descriptionIcon($growthServiceBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthServiceBulan >= 0 ? 'success' : 'danger')
                ->chart($serviceChart),
        ];
    }
}
