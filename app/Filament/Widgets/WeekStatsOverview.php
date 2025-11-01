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
        // $startWeekDate = Carbon::now()->startOfWeek();
        // $endWeekDate = Carbon::now()->endOfWeek()->addDay();

        // // Minggu lalu
        // $startLastWeek = Carbon::now()->subWeek()->startOfWeek();
        // $endLastWeek = Carbon::now()->subWeek()->endOfWeek()->addDay();

        // Gunakan satu waktu acuan
        $now = Carbon::now();

        // Minggu ini
        $startWeekDate = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endWeekDate   = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        // Minggu lalu
        $startLastWeek = $now->copy()->subWeek()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endLastWeek   = $now->copy()->subWeek()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        // Optional: buat string format untuk whereBetween (kalau field di DB bertipe DATE)
        $startWeekStr = $startWeekDate->toDateTimeString();
        $endWeekStr = $endWeekDate->toDateTimeString();
        $startLastWeekStr = $startLastWeek->toDateTimeString();
        $endLastWeekStr = $endLastWeek->toDateTimeString();

        // ==================== //
        //   Data Minggu Lalu   //
        // ==================== //
        $laba_kotor_minggu_lalu = LabaRugi::getTotalPendapatan($startLastWeekStr, $endLastWeekStr)[0]->jumlah ?? 0;

        $item_terjual_minggu_lalu = DB::table('inventories')
        ->selectRaw('SUM(CASE WHEN movement_type = "OUT-SAL" THEN jumlah_terkecil ELSE 0 END) as total_qty_terjual')
        ->whereBetween('tanggal_transaksi', [$startLastWeek, $endLastWeek])
            ->value('total_qty_terjual') ?? 0;

        $service_selesai_minggu_lalu = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('approved_at', [$startLastWeek, $endLastWeek])
            ->count();

        // ==================== //
        //   Data Minggu Ini    //
        // ==================== //
        $laba_kotor_minggu = LabaRugi::getTotalPendapatan($startWeekStr, $endWeekStr)[0]->jumlah ?? 0;

        $item_terjual_minggu = DB::table('inventories')
        ->selectRaw('SUM(CASE WHEN movement_type = "OUT-SAL" THEN jumlah_terkecil ELSE 0 END) as total_qty_terjual')
        ->whereBetween('tanggal_transaksi', [$startWeekDate, $endWeekDate])
            ->value('total_qty_terjual') ?? 0;

        $service_selesai_minggu = DB::table('service_schedules')
        ->where('is_approve', 'approved')
        ->whereBetween('approved_at', [$startWeekDate, $endWeekDate])
            ->count();

        // ==================== //
        //     Hitung Growth    //
        // ==================== //
        $growthLabaMinggu = $laba_kotor_minggu - $laba_kotor_minggu_lalu;
        $growthItemMinggu = $item_terjual_minggu - $item_terjual_minggu_lalu;
        $growthServiceMinggu = $service_selesai_minggu - $service_selesai_minggu_lalu;

        // ==================== //
        //     Mini Chart Data  //
        // ==================== //
        $tanggalMingguan = collect(range(0, 6))->map(fn ($i) => $startWeekDate->copy()->addDays($i)->format('Y-m-d'));

        $labaChart = $tanggalMingguan->map(function ($tanggal) {
            return (float) LabaRugi::getTotalPendapatan($tanggal, $tanggal. ' 23:59:59')[0]->jumlah ?? 0;
        })->toArray();

        $itemChart = $tanggalMingguan->map(function ($tanggal) {
            return (float) DB::table('inventories')
            ->where('movement_type', 'OUT-SAL')
            ->whereDate('tanggal_transaksi', $tanggal)
                ->sum('jumlah_terkecil');
        })->toArray();
        // dd($tanggalMingguan);

        $serviceChart = $tanggalMingguan->map(function ($tanggal) {
            return (float) DB::table('service_schedules')
            ->where('is_approve', 'approved')
            ->whereDate('approved_at', $tanggal)
                ->count();
        })->toArray();

        return [
            Stat::make('Pendapatan Minggu Ini', 'Rp ' . number_format($laba_kotor_minggu, 0, ',', '.'))
                ->description('Perubahan: Rp ' . number_format($growthLabaMinggu, 0, ',', '.'))
                ->descriptionIcon($growthLabaMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthLabaMinggu >= 0 ? 'success' : 'danger')
                ->chart($labaChart),

            Stat::make('Item Terjual Minggu Ini', number_format($item_terjual_minggu, 0, ',', '.'))
                ->description('Perubahan: ' . number_format($growthItemMinggu, 0, ',', '.') . ' item')
                ->descriptionIcon($growthItemMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthItemMinggu >= 0 ? 'success' : 'danger')
                ->chart($itemChart),

            Stat::make('Service Selesai Minggu Ini', $service_selesai_minggu)
                ->description('Perubahan: ' . number_format($growthServiceMinggu, 0, ',', '.') . ' service')
                ->descriptionIcon($growthServiceMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthServiceMinggu >= 0 ? 'success' : 'danger')
                ->chart($serviceChart),
        ];
    }
}
