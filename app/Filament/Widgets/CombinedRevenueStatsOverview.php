<?php

namespace App\Filament\Widgets;

use App\Models\LabaRugi;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CombinedRevenueStatsOverview extends BaseWidget
{
    protected static ?int $sort = 20;

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        $now = Carbon::now();

        $startMonth = $now->copy()->startOfMonth()->startOfDay();
        $endMonth = $now->copy()->endOfMonth()->endOfDay();

        $startWeek = $now->copy()->startOfMonth()
            ->addDays(floor(($now->day - 1) / 7) * 7)
            ->startOfDay();
        $endWeek = $startWeek->copy()->addDays(6)->endOfDay();

        $startLastWeek = $startWeek->copy()->subWeek();
        $endLastWeek = $endWeek->copy()->subWeek();

        $startLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
        $endLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();

        $pendapatanMingguIni = LabaRugi::getTotalPendapatan($startWeek->toDateTimeString(), $endWeek->toDateTimeString())[0]->jumlah ?? 0;
        $pendapatanMingguLalu = LabaRugi::getTotalPendapatan($startLastWeek->toDateTimeString(), $endLastWeek->toDateTimeString())[0]->jumlah ?? 0;
        $growthMinggu = $pendapatanMingguIni - $pendapatanMingguLalu;

        $pendapatanBulanIni = LabaRugi::getTotalPendapatan($startMonth->toDateTimeString(), $endMonth->toDateTimeString())[0]->jumlah ?? 0;
        $pendapatanBulanLalu = LabaRugi::getTotalPendapatan($startLastMonth->toDateTimeString(), $endLastMonth->toDateTimeString())[0]->jumlah ?? 0;
        $growthBulan = $pendapatanBulanIni - $pendapatanBulanLalu;

        $discountBulanIni = LabaRugi::getTotalDiscount($startMonth->toDateTimeString(), $endMonth->toDateTimeString())[0]->jumlah ?? 0;
        $discountBulanLalu = LabaRugi::getTotalDiscount($startLastMonth->toDateTimeString(), $endLastMonth->toDateTimeString())[0]->jumlah ?? 0;
        $growthDiscountBulan = $discountBulanIni - $discountBulanLalu;

        $weeklyLabels = collect(range(0, 6))->map(fn ($i) => $startWeek->copy()->addDays($i)->format('Y-m-d'));
        $weeklyChart = $weeklyLabels->map(fn ($tanggal) =>
            (float) LabaRugi::getTotalPendapatan($tanggal, $tanggal . ' 23:59:59')[0]->jumlah ?? 0
        )->toArray();

        $monthlyLabels = collect(range(0, $now->day - 1))->map(fn ($i) => $startMonth->copy()->addDays($i)->format('Y-m-d'));
        $monthlyChart = $monthlyLabels->map(fn ($tanggal) =>
            (float) LabaRugi::getTotalPendapatan($tanggal, $tanggal . ' 23:59:59')[0]->jumlah ?? 0
        )->toArray();

        $monthlyDiscountLabels = collect(range(0, $now->day - 1))->map(fn ($i) => $startMonth->copy()->addDays($i)->format('Y-m-d'));
        $monthlyDiscountChart = $monthlyDiscountLabels->map(fn ($tanggal) =>
            (float) LabaRugi::getTotalDiscount($tanggal, $tanggal . ' 23:59:59')[0]->jumlah ?? 0
        )->toArray();

        return [
            Stat::make('Pendapatan Minggu Ini', 'Rp ' . number_format($pendapatanMingguIni, 0, ',', '.'))
                ->description('Perubahan: Rp ' . number_format($growthMinggu, 0, ',', '.'))
                ->descriptionIcon($growthMinggu >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthMinggu >= 0 ? 'success' : 'danger')
                ->chart($weeklyChart),

            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($pendapatanBulanIni, 0, ',', '.'))
                ->description('Perubahan: Rp ' . number_format($growthBulan, 0, ',', '.'))
                ->descriptionIcon($growthBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthBulan >= 0 ? 'success' : 'danger')
                ->chart($monthlyChart),

            Stat::make('Discount Bulan Ini', 'Rp ' . number_format($discountBulanIni, 0, ',', '.'))
                ->description('Perubahan: Rp ' . number_format($growthDiscountBulan, 0, ',', '.'))
                ->descriptionIcon($growthDiscountBulan >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growthDiscountBulan >= 0 ? 'danger' : 'success') // Discount naik itu buruk, jadi warnanya dibalik
                ->chart($monthlyDiscountChart),
        ];
    }
}
