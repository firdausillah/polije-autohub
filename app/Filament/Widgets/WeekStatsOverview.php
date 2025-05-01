<?php

namespace App\Filament\Widgets;

use App\Models\LabaRugi;
use App\Models\SalesReport;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class WeekStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;


    protected function getStats(): array
    {
        // Minggu ini
        $startWeekDate = Carbon::now()->startOfWeek();
        $endWeekDate = Carbon::now()->endOfWeek()->addDay();

        // Minggu lalu
        $startLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endLastWeek = Carbon::now()->subWeek()->endOfWeek()->addDay();

        // ==================== //
        //   Data Minggu Lalu    //
        // ==================== //
        $laba_kotor_minggu_lalu = LabaRugi::getLabaKotor($startLastWeek->toDateString(), $endLastWeek->toDateString())[0]['jumlah'] ?? 0;
        $item_terjual_minggu_lalu = DB::table('inventories')
        ->selectRaw('SUM(CASE WHEN movement_type = "OUT-SAL" THEN jumlah_terkecil ELSE 0 END) as total_qty_terjual')
        ->whereBetween('created_at', [$startLastWeek, $endLastWeek])
            ->value('total_qty_terjual') ?? 0;

        // ==================== //
        //   Data Minggu Lalu   //
        // ==================== //
        $service_selesai_minggu_lalu = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('created_at', [$startLastWeek, $endLastWeek])
            ->count();

        // ==================== //
        //     Data Sekarang    //
        // ==================== //
        $laba_kotor_Minggu = LabaRugi::getLabaKotor($startWeekDate->toDateString(), $endWeekDate->toDateString())[0]['jumlah'] ?? 0;
        $item_terjual_Minggu = DB::table('inventories')
        ->selectRaw('SUM(CASE WHEN movement_type = "OUT-SAL" THEN jumlah_terkecil ELSE 0 END) as total_qty_terjual')
        ->whereBetween('created_at', [$startWeekDate, $endWeekDate])
            ->value('total_qty_terjual') ?? 0;
        $service_selesai_minggu = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('created_at', [$startWeekDate, $endWeekDate])
            ->count();

        // ==================== //
        //     Hitung Growth    //
        // ==================== //
        $growthLabaMinggu = $laba_kotor_Minggu - $laba_kotor_minggu_lalu;
        $growthItemMinggu = $item_terjual_Minggu - $item_terjual_minggu_lalu;
        $growthServiceMinggu = $service_selesai_minggu - $service_selesai_minggu_lalu;

        return [
            Stat::make('Pendapatan Minggu Ini', 'Rp ' . number_format($laba_kotor_Minggu, 2, ',', '.'))
                ->description('Perubahan: Rp ' . number_format($growthLabaMinggu, 0, ',', '.'))
                ->descriptionIcon($growthLabaMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthLabaMinggu >= 0 ? 'success' : 'danger'),

            Stat::make('Item Terjual Minggu Ini', number_format($item_terjual_Minggu, 0, ',', '.'))
                ->description('Perubahan: ' . number_format($growthItemMinggu, 0, ',', '.') . ' item')
                ->descriptionIcon($growthItemMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthItemMinggu >= 0 ? 'success' : 'danger'),

            Stat::make('Service Selesai Minggu Ini', $service_selesai_minggu)
                ->description('Perubahan: ' . number_format($growthServiceMinggu, 0, ',', '.') . ' service')
                ->descriptionIcon($growthServiceMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthServiceMinggu >= 0 ? 'success' : 'danger'),
        ];
    }
}
