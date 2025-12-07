<?php

namespace App\Filament\Widgets;

use App\Models\SalesReport;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSales extends BaseWidget
{
    protected static ?int $sort = 40;

    // public static function canView(): bool
    // {
    //     return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager','Admin', 'Kepala Unit']);
    // }

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['Admin']);
    }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    public function table(Table $table): Table
    {

        // Bulan ini
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        return $table
            ->paginated(false)
            ->query(
                SalesReport::query()->limit(5)->orderBy('qty_terjual', 'desc')
            )
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('qty_terjual'),
                TextColumn::make('saldo')
                ->label('Saldo Barang'),
            ]);
    }
}
