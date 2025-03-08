<?php

namespace App\Filament\Pages;

use App\Models\Inventory;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

class KartuStok extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.kartu-stok';

    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
        ->query(
            Inventory::kartuStok() // Hasilnya array of objects
        )
        ->defaultGroup('sparepart')
        ->columns([
            TextColumn::make('tanggal_transaksi'),
            TextColumn::make('transaksi_kode'),
            TextColumn::make('sparepart_name'),
            TextColumn::make('sparepart_kode'),
            TextColumn::make('qty_masuk')
                    ->label('Qty Masuk')
                    ->formatStateUsing(fn ($record) => $record->movement_type === 'IN-PUR' ? $record->jumlah_terkecil : 0),
            TextColumn::make('qty_keluar')
                    ->label('Qty Keluar')
                    ->formatStateUsing(fn ($record) => $record->movement_type === 'OUT-SAL' ? $record->jumlah_terkecil : 0),
            TextColumn::make('saldo'),
        ])
        ->filters([
            SelectFilter::make('sparepart_id')
            ->label('Sparepart')
                ->relationship('sparepart', 'name'),
            Filter::make('tanggal')
                ->form([
                    DatePicker::make('start_date')->label('Dari Tanggal'),
                    DatePicker::make('end_date')->label('Sampai Tanggal'),
                ])
                ->query(function ($query, array $data) {
                    if ($data['start_date'] ?? null) {
                        $query->whereDate('tanggal_transaksi', '>=', $data['start_date']);
                    }
                    if ($data['end_date'] ?? null) {
                        $query->whereDate('tanggal_transaksi', '<=', $data['end_date']);
                    }
                })
        ])

        ->actions([
            // ...
        ])
        ->bulkActions([
            // ...
        ]);

    }
}