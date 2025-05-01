<?php

namespace App\Filament\Widgets;

use App\Models\SalesReport;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StokTerkecil extends BaseWidget
{
    protected static ?int $sort = 4;

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
