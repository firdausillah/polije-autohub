<?php

namespace App\Filament\Pages;

use App\Models\JsonData;
use App\Models\Sparepart;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
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
use Illuminate\Http\Request;

class KartuStok extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.kartu-stok';

    protected static ?string $navigationGroup = 'Laporan';

    public ?array $filters = [];

    public function mount(): void
    {
        $this->filters = [
            'sparepart_id' => null,
            'tanggal_awal' => now()->startOfMonth()->toDateString(),
            'tanggal_akhir' => now()->endOfMonth()->toDateString(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(JsonData::getFilteredQuery())
            ->columns([
                TextColumn::make('tanggal_transaksi')
                ->formatStateUsing(fn ($state) => Carbon::parse($state)->translatedFormat('d M Y')),
                TextColumn::make('transaksi_kode'),
                TextColumn::make('satuan'),
                TextColumn::make('qty_masuk'),
                TextColumn::make('qty_keluar'),
                TextColumn::make('saldo'),
            ])
            ->filters([
                SelectFilter::make('sparepart_id')
                ->options(fn () => \App\Models\Sparepart::pluck('name', 'id')->toArray()),
                Filter::make('tanggal')
                ->form([
                    DatePicker::make('tanggal_awal')->live(),
                    DatePicker::make('tanggal_akhir')->live(),
                ]),
            ]);

    }
}
