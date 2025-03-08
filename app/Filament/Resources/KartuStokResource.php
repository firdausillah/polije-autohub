<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KartuStokResource\Pages;
use App\Models\Inventory;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class KartuStokResource extends Resource
{
    protected static ?string $model = Inventory::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Kartu Stok';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                Inventory::query()->orderBy('tanggal_transaksi')->orderBy('transaksi_h_id')->orderBy('transaksi_d_id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaksi_h_kode')->label('Transaksi Kode'),
                Tables\Columns\TextColumn::make('tanggal_transaksi')->label('Tanggal')->date(),
                Tables\Columns\TextColumn::make('sparepart_name')->label('Sparepart'),
                Tables\Columns\TextColumn::make('sparepart_kode')->label('Kode Sparepart'),
                Tables\Columns\TextColumn::make('satuan_terkecil_name')->label('Satuan'),
                Tables\Columns\TextColumn::make('relation_name')->label('Supplier/Pelanggan')->default('-'),
                Tables\Columns\TextColumn::make('qty_masuk')
                    ->label('Qty Masuk')
                    ->formatStateUsing(fn ($record) => $record->movement_type === 'IN-PUR' ? $record->jumlah_terkecil : 0),
                Tables\Columns\TextColumn::make('qty_keluar')
                    ->label('Qty Keluar')
                    ->formatStateUsing(fn ($record) => $record->movement_type === 'OUT-SAL' ? $record->jumlah_terkecil : 0),
                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo Stok')
                    ->getStateUsing(fn ($record) => static::hitungSaldo($record)),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('end_date')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['start_date'], fn ($q) => $q->where('tanggal_transaksi', '>=', $data['start_date']))
                            ->when($data['end_date'], fn ($q) => $q->where('tanggal_transaksi', '<=', $data['end_date']));
                    }),
                Tables\Filters\SelectFilter::make('sparepart_id')
                    ->label('Sparepart')
                    ->relationship('sparepart', 'name'), // Sesuaikan dengan relasi model
            ])
            ->defaultSort('tanggal_transaksi', 'asc');
    }

    private static function hitungSaldo($record)
    {

        $saldoSebelumnya = Inventory::where('sparepart_id', $record->sparepart_id)
            ->where('tanggal_transaksi', '<', $record->tanggal_transaksi)
            ->sum('jumlah_terkecil');
        

        $saldoSaatIni = $saldoSebelumnya + ($record->movement_type === 'IN-PUR' ? $record->jumlah_terkecil : -$record->jumlah_terkecil);

        return $saldoSaatIni;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKartuStoks::route('/'),
        ];
    }
}
