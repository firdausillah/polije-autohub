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
                TextColumn::make('service_pendapatan_ini')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->color(fn($record)=> $record->service_perbandingan_persen <= 100 ? 'danger' : 'success')
                ->description(function ($record) {
                    // $arrow = $record->service_perbandingan_persen >= 100 ? '▲' : '▼';

                    return "L: Rp " . number_format($record->service_pendapatan_lalu) .
                        " | {$record->service_perbandingan_persen}%";
                })
                ->label('Service'),
                TextColumn::make('part_pendapatan_ini')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->color(fn($record)=> $record->part_perbandingan_persen <= 100 ? 'danger' : 'success')
                ->description(function ($record) {
                    // $arrow = $record->part_perbandingan_persen >= 100 ? '▲' : '▼';

                    return "L: Rp " . number_format($record->part_pendapatan_lalu) .
                        " | {$record->part_perbandingan_persen}%";
                })
                ->label('Sparepart'),
                TextColumn::make('liquid_pendapatan_ini')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->color(fn($record)=> $record->liquid_perbandingan_persen <= 100 ? 'danger' : 'success')
                ->description(function ($record) {
                    // $arrow = $record->liquid_perbandingan_persen >= 100 ? '▲' : '▼';

                    return "L: Rp " . number_format($record->liquid_pendapatan_lalu) .
                        " | {$record->liquid_perbandingan_persen}%";
                })
                ->label('Liquid'),
                TextColumn::make('total_ini')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                ->color(fn($record)=> $record->total_perbandingan_persen <= 100 ? 'danger' : 'success')
                ->description(function ($record) {
                    // $arrow = $record->total_perbandingan_persen >= 100 ? '▲' : '▼';

                    return "L: Rp " . number_format($record->total_lalu) .
                        " | {$record->total_perbandingan_persen}%";
                })
                ->label('Total'),
            ]);
    }

}
