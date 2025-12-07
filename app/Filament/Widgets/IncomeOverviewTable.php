<?php

namespace App\Filament\Widgets;

use App\Models\IncomeOverviews;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncomeOverviewTable extends TableWidget
{
    protected static ?string $heading = 'Table Pendapatan per Mekanik';

    protected static ?int $sort = 40;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['Kepala Unit']);
    }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }


    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->query(
                // SalesReport::query()->limit(5)->orderBy('saldo', 'asc')
                IncomeOverviews::query()->where('keterangan', 'Mekanik')->whereNot('id', Auth::id())
            )
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('service_perbandingan_persen')
                ->suffix('%')
                ->color(fn($state)=> $state <= 100 ? 'danger' : 'success')
                ->label('Service'),
                TextColumn::make('part_perbandingan_persen')
                ->suffix('%')
                ->color(fn($state)=> $state <= 100 ? 'danger' : 'success')
                ->label('Sparepart'),
                TextColumn::make('liquid_perbandingan_persen')
                ->suffix('%')
                ->color(fn($state)=> $state <= 100 ? 'danger' : 'success')
                ->label('Liquid'),
                TextColumn::make('total_perbandingan_persen')
                ->suffix('%')
                ->color(fn($state)=> $state <= 100 ? 'danger' : 'success')
                ->label('Total'),
            ]);
    }

}
