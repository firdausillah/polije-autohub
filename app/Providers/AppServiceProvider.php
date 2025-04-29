<?php

namespace App\Providers;

use App\Filament\Reports\KartuStok;
use App\Filament\Reports\LabaRugi;
use App\Filament\Reports\PemasukanPengeluaran;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
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
            // Pastikan user login & punya role super_admin
            if (auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin'])) {
                Filament::registerNavigationItems([
                    NavigationItem::make()
                        ->label('Laba Rugi')
                        ->url(LabaRugi::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->group('Laporan')
                        ->isActiveWhen(fn () => str_contains(request()->url(), LabaRugi::getUrl())),
                    NavigationItem::make()
                        ->label('Kartu Stok')
                        ->url(KartuStok::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->group('Laporan')
                        ->isActiveWhen(fn () => str_contains(request()->url(), KartuStok::getUrl())),
                    NavigationItem::make()
                        ->label('Pemasukan & Pengeluaran')
                        ->url(PemasukanPengeluaran::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->group('Laporan')
                        ->isActiveWhen(fn () => str_contains(request()->url(), PemasukanPengeluaran::getUrl())),
                ]);
            }
        });
    }
}
