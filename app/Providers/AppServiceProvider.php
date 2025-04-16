<?php

namespace App\Providers;

use App\Filament\Reports\KartuStok;
use App\Filament\Reports\LabaRugi;
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
            if (auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager'])) {
                Filament::registerNavigationItems([
                    NavigationItem::make()
                        ->label('Laba Rugi')
                        ->url(LabaRugi::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->group('Laporan'),
                    NavigationItem::make()
                        ->label('Kartu Stok')
                        ->url(KartuStok::getUrl())
                        ->icon('heroicon-o-document-text')
                        ->group('Laporan'),
                ]);
            }
        });
    }
}
