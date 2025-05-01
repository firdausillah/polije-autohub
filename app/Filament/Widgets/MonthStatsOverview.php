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
        // Bulan ini
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth()->addDay();

        // Bulan lalu
        $startLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endLastMonth = Carbon::now()->subMonth()->endOfMonth()->addDay();

        // ==================== //
        //   Data Bulan Lalu    //
        // ==================== //
        $laba_kotor_bulan_lalu = LabaRugi::getLabaKotor($startLastMonth->toDateString(), $endLastMonth->toDateString())[0]['jumlah'] ?? 0;
        $item_terjual_bulan_lalu = DB::table('inventories')
        ->selectRaw('SUM(CASE WHEN movement_type = "OUT-SAL" THEN jumlah_terkecil ELSE 0 END) as total_qty_terjual')
        ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->value('total_qty_terjual') ?? 0;

        // ==================== //
        //   Data  Bulan Lalu   //
        // ==================== //
        $laba_kotor_bulan_lalu = LabaRugi::getLabaKotor($startDate->toDateString(), $endDate->toDateString())[0]['jumlah'] ?? 0;
        $service_selesai_bulan_lalu = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // ==================== //
        //     Data Sekarang    //
        // ==================== //
        $laba_kotor_bulan = LabaRugi::getLabaKotor($startDate->toDateString(), $endDate->toDateString())[0]['jumlah'] ?? 0;
        $item_terjual_bulan = DB::table('inventories')
        ->selectRaw('SUM(CASE WHEN movement_type = "OUT-SAL" THEN jumlah_terkecil ELSE 0 END) as total_qty_terjual')
        ->whereBetween('created_at', [$startDate, $endDate])
            ->value('total_qty_terjual') ?? 0;
        $laba_kotor_bulan = LabaRugi::getLabaKotor($startDate->toDateString(), $endDate->toDateString())[0]['jumlah'] ?? 0;
        $service_selesai_bulan = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // ==================== //
        //     Hitung Growth    //
        // ==================== //
        $growthLabaBulan = $laba_kotor_bulan - $laba_kotor_bulan_lalu;
        $growthItemBulan = $item_terjual_bulan - $item_terjual_bulan_lalu;
        $growthServiceBulan = $service_selesai_bulan - $service_selesai_bulan_lalu;

        return [
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($laba_kotor_bulan, 2, ',', '.'))
                ->description('Perubahan: Rp ' . number_format($growthLabaBulan, 0, ',', '.'))
                ->descriptionIcon($growthLabaBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthLabaBulan >= 0 ? 'success' : 'danger'),

            Stat::make('Item Terjual Bulan Ini', number_format($item_terjual_bulan, 0, ',', '.'))
                ->description('Perubahan: ' . number_format($growthItemBulan, 0, ',', '.') . ' item')
                ->descriptionIcon($growthItemBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthItemBulan >= 0 ? 'success' : 'danger'),

            Stat::make('Service Selesai Bulan Ini', $service_selesai_bulan)
                ->description('Perubahan: ' . number_format($growthServiceBulan, 0, ',', '.') . ' service')
                ->descriptionIcon($growthServiceBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthServiceBulan >= 0 ? 'success' : 'danger'),
        ];
    }
}
