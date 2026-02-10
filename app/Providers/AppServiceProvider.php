<?php

namespace App\Providers;

use App\Filament\Reports\KartuStok;
use App\Filament\Reports\LabaRugi;
use App\Filament\Reports\LaporanPenjualan;
use App\Filament\Reports\PemasukanPengeluaran;
use App\Filament\Reports\UserIncomeReport;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        Filament::serving(function () {
            if (!auth()->check()) {
                return; // kalau belum login, jangan load apa-apa
            }

            $user = auth()->user();

            if ($user->hasAnyRole(['super_admin', 'Manager', 'Admin', 'pimpinan'])) {
                Filament::registerNavigationItems([
                    NavigationItem::make()
                        ->label('Laba Rugi')
                        ->url(LabaRugi::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->isActiveWhen(fn () => request()->routeIs(LabaRugi::getRouteName()))
                        ->group('Laporan'),

                ]);
            }

            if ($user->hasAnyRole(['super_admin', 'Manager', 'Admin'])) {
                Filament::registerNavigationItems([
                    NavigationItem::make()
                        ->label('Kartu Stok')
                        ->url(KartuStok::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->isActiveWhen(fn () => request()->routeIs(KartuStok::getRouteName()))
                        ->group('Laporan'),
                ]);
            }

            if ($user->hasAnyRole(['super_admin', 'Manager', 'Admin', 'Kepala Unit'])) {
                Filament::registerNavigationItems([
                    NavigationItem::make()
                        ->label('Perbandingan Pendapatan')
                        ->url(UserIncomeReport::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->isActiveWhen(fn () => request()->routeIs(UserIncomeReport::getRouteName()))
                        ->group('Laporan'),
                    NavigationItem::make()
                        ->label('Pemasukan & Pengeluaran')
                        ->url(PemasukanPengeluaran::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->isActiveWhen(fn () => request()->routeIs(PemasukanPengeluaran::getRouteName()))
                        ->group('Laporan'),
                    NavigationItem::make()
                        ->label('Laporan Penjualan')
                        ->url(LaporanPenjualan::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->isActiveWhen(fn () => request()->routeIs(LaporanPenjualan::getRouteName()))
                        ->group('Laporan'),
                ]);
            }
        });

        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => view('partials.session-expired-auto-reload')->render()
        );
        
    }
}
