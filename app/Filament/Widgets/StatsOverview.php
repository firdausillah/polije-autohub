<?php

namespace App\Filament\Widgets;

use App\Models\LabaRugi;
use App\Models\SalesReport;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;


    protected function getStats(): array
    {
        // Bulan ini
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Bulan lalu
        $startLastMonth = Carbon::now()->subMonth()->startOfMonth();
        $endLastMonth = Carbon::now()->subMonth()->endOfMonth();

        // Minggu ini
        $startWeekDate = Carbon::now()->startOfWeek();
        $endWeekDate = Carbon::now()->endOfWeek();

        // Minggu lalu
        $startLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endLastWeek = Carbon::now()->subWeek()->endOfWeek();

        // ==================== //
        //   Data Bulan Lalu    //
        // ==================== //
        $laba_kotor_bulan_lalu = LabaRugi::getLabaKotor($startLastMonth->toDateString(), $endLastMonth->toDateString())[0]['jumlah'] ?? 0;
        $item_terjual_bulan_lalu = DB::table('inventories')
        ->selectRaw('SUM(CASE WHEN movement_type = "OUT-SAL" THEN jumlah_terkecil ELSE 0 END) as total_qty_terjual')
        ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->value('total_qty_terjual') ?? 0;

        // ==================== //
        //   Data Minggu Lalu   //
        // ==================== //
        $laba_kotor_minggu_lalu = LabaRugi::getLabaKotor($startLastWeek->toDateString(), $endLastWeek->toDateString())[0]['jumlah'] ?? 0;
        $service_selesai_minggu_lalu = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('created_at', [$startLastWeek, $endLastWeek])
            ->count();

        // ==================== //
        //     Data Sekarang    //
        // ==================== //
        $laba_kotor_bulan = LabaRugi::getLabaKotor($startDate->toDateString(), $endDate->toDateString())[0]['jumlah'] ?? 0;
        $item_terjual_bulan = DB::table('inventories')
        ->selectRaw('SUM(CASE WHEN movement_type = "OUT-SAL" THEN jumlah_terkecil ELSE 0 END) as total_qty_terjual')
        ->whereBetween('created_at', [$startDate, $endDate])
            ->value('total_qty_terjual') ?? 0;
        $laba_kotor_minggu = LabaRugi::getLabaKotor($startWeekDate->toDateString(), $endWeekDate->toDateString())[0]['jumlah'] ?? 0;
        $service_selesai_minggu = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('created_at', [$startWeekDate, $endWeekDate])
            ->count();

        // ==================== //
        //     Hitung Growth    //
        // ==================== //
        $growthLabaBulan = $laba_kotor_bulan - $laba_kotor_bulan_lalu;
        $growthLabaMinggu = $laba_kotor_minggu - $laba_kotor_minggu_lalu;
        $growthItemBulan = $item_terjual_bulan - $item_terjual_bulan_lalu;
        $growthServiceMinggu = $service_selesai_minggu - $service_selesai_minggu_lalu;

        return [
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($laba_kotor_bulan, 2, ',', '.'))
                ->description('Perubahan: Rp ' . number_format($growthLabaBulan, 0, ',', '.'))
                ->descriptionIcon($growthLabaBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthLabaBulan >= 0 ? 'success' : 'danger'),

            Stat::make('Laba Kotor Minggu Ini', 'Rp ' . number_format($laba_kotor_minggu, 2, ',', '.'))
                ->description('Perubahan: Rp ' . number_format($growthLabaMinggu, 2, ',', '.'))
                ->descriptionIcon($growthLabaMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthLabaMinggu >= 0 ? 'success' : 'danger'),

            Stat::make('Item Terjual Bulan Ini', number_format($item_terjual_bulan, 0, ',', '.'))
                ->description('Perubahan: ' . number_format($growthItemBulan, 0, ',', '.') . ' item')
                ->descriptionIcon($growthItemBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthItemBulan >= 0 ? 'success' : 'danger'),

            Stat::make('Service Selesai Minggu Ini', $service_selesai_minggu)
                ->description('Perubahan: ' . number_format($growthServiceMinggu, 0, ',', '.') . ' service')
                ->descriptionIcon($growthServiceMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthServiceMinggu >= 0 ? 'success' : 'danger'),
        ];
    }
}
