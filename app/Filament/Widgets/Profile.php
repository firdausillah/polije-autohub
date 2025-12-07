<?php

namespace App\Filament\Widgets;

use App\Models\LabaRugi;
use App\Models\LastMonthIncomeComparison;
use App\Models\SalesReport;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

class Profile extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 1;
    }

    protected function getPollingInterval(): ?string
    {
        return null; // no polling
    }

    protected function getStats(): array
    {

        return [
            Stat::make('', User::find(Auth::id())->name)
            ->description(date_format(NOW(), 'd F Y'))
            // ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('info')
            // ->chart($labaChart),
        ];
    }
}
