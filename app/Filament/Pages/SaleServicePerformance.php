<?php

namespace App\Filament\Pages;

use App\Models\UserPayroll;
use Filament\Tables;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SaleServicePerformance extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.sale-service-performance';

    protected static ?string $navigationGroup = 'Laporan';

    public ?Carbon $tanggal_awal = null;
    public ?Carbon $tanggal_akhir = null;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin']);
    }

    protected function getTableQuery(): Builder
    {
        $query = UserPayroll::query()
            ->withSum(['payrollJurnals as pendapatan_rp' => function ($q) {
                $q->where('is_dibayar', 0);
                if ($this->tanggal_awal) {
                    $q->whereDate('created_at', '>=', $this->tanggal_awal);
                }
                if ($this->tanggal_akhir) {
                    $q->whereDate('created_at', '<=', $this->tanggal_akhir);
                }
            }], 'nominal')
            ->withCount(['payrollJurnals as total_unit' => function ($q) {
                $q->where('is_dibayar', 0);
                if ($this->tanggal_awal) {
                    $q->whereDate('created_at', '>=', $this->tanggal_awal);
                }
                if ($this->tanggal_akhir) {
                    $q->whereDate('created_at', '<=', $this->tanggal_akhir);
                }
            }])
            ->withSum(['payrollJurnals as total_jasa' => function ($q) {
                $q->where('is_dibayar', 0);
                if ($this->tanggal_awal) {
                    $q->whereDate('created_at', '>=', $this->tanggal_awal);
                }
                if ($this->tanggal_akhir) {
                    $q->whereDate('created_at', '<=', $this->tanggal_akhir);
                }
            }], 'jumlah_service')
            ->withSum(['payrollJurnals as total_sparepart' => function ($q) {
                $q->where('is_dibayar', 0);
                if ($this->tanggal_awal) {
                    $q->whereDate('created_at', '>=', $this->tanggal_awal);
                }
                if ($this->tanggal_akhir) {
                    $q->whereDate('created_at', '<=', $this->tanggal_akhir);
                }
            }], 'jumlah_sparepart');

        return $query;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getTableQuery())
            ->columns([
                TextColumn::make('user_name')->label('Nama User'),
                TextColumn::make('payrole_name')->label('Posisi'),
                TextColumn::make('total_unit')->label('Total Unit'),
                TextColumn::make('total_jasa')->label('Total Jasa'),
                TextColumn::make('total_sparepart')->label('Total Sparepart'),
                TextColumn::make('pendapatan_rp')->label('Pendapatan')->money('IDR', locale: 'id_ID'),
                TextColumn::make('gaji_pokok')->label('Gaji')->money('IDR', locale: 'id_ID'),
                TextColumn::make('bonus_rp')->label('Bonus')->money('IDR', locale: 'id_ID')->getStateUsing(function ($record) {
                    $pendapatan = $record->pendapatan_rp ?? 0;
                    $minBonus = $record->min_bonus ?? 0;
                    $persenBonus = $record->persentase_bonus ?? 0;

                    if ($pendapatan <= $minBonus) {
                        return 0;
                    }
                    return $pendapatan * $persenBonus / 100;
                }),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('tanggal_awal')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfMonth())
                            ->reactive()
                            ->afterStateUpdated(fn ($state) => $this->tanggal_awal = $state),
                        DatePicker::make('tanggal_akhir')
                            ->label('Sampai Tanggal')
                            ->default(now()->endOfMonth())
                            ->reactive()
                            ->afterStateUpdated(fn ($state) => $this->tanggal_akhir = $state),
                    ])
                    ->query(fn ($query, array $data) => $query) // no-op here, filter applied in getTableQuery
            ]);
    }
}
