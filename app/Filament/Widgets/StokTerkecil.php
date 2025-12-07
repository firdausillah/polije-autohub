<?php

namespace App\Filament\Widgets;

use App\Models\SalesReport;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StokTerkecil extends BaseWidget
{
    protected static ?int $sort = 40;

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    // public static function canView(): bool
    // {
    //     return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin', 'Kepala Unit']);
    // }

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['Admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->query(
                SalesReport::query()->limit(5)->orderBy('saldo', 'asc')
            )
            ->columns([
                TextColumn::make('name')
                ->color(fn(SalesReport $salesReport)=> $salesReport->saldo <= 0 ? 'danger' : 'dark'),
                TextColumn::make('saldo')
                    ->label('Saldo Barang'),
            ]);
    }
}
