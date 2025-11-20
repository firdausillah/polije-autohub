<?php

namespace App\Filament\Pages;

use App\Models\PayrollJurnal;
use App\Models\User;
use App\Models\UserPayrole;
use App\Models\UserPayroll;
use App\Models\UserRole;
use Filament\Tables;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SaleServicePerformance extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.sale-service-performance';

    protected static ?string $navigationGroup = 'Laporan';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['super_admin', 'Manager', 'Admin']);
    }


    public function table(Table $table): Table
    {
        return $table
        ->query(
            fn (): Builder => UserPayroll::query()
                ->select([
                    'user_payroll.id',
                    'user_payroll.user_name',
                    'user_payroll.payrole_name',

                    // Total pendapatan
                    DB::raw("SUM(IFNULL(payroll_jurnals.nominal, 0)) as pendapatan_rp"),

                    // Gaji sesuai logika baru
                    DB::raw("
                CASE 
                    WHEN SUM(IFNULL(payroll_jurnals.nominal, 0)) <= user_payroll.min_gaji
                        THEN SUM(IFNULL(payroll_jurnals.nominal, 0)) / 2
                    ELSE user_payroll.gaji_pokok
                END as gaji_rp
            "),

                    // Bonus sesuai logika baru
                    DB::raw("
                CASE 
                    WHEN SUM(IFNULL(payroll_jurnals.nominal, 0)) <= user_payroll.min_bonus
                        THEN 0
                    ELSE SUM(IFNULL(payroll_jurnals.nominal, 0)) * user_payroll.persentase_bonus / 100
                END as bonus_rp
            "),

                    DB::raw("COUNT(payroll_jurnals.id) as total_unit"),
                    DB::raw("SUM(IFNULL(payroll_jurnals.jumlah_service, 0)) as total_jasa"),
                    DB::raw("SUM(IFNULL(payroll_jurnals.jumlah_sparepart, 0)) as total_sparepart"),
                ])
                ->leftJoin('payroll_jurnals', function ($join) {
                    $join->on('user_payroll.id', '=', 'payroll_jurnals.user_id')
                        ->where('payroll_jurnals.is_dibayar', '=', 0);
                })
                ->groupBy(
                    'user_payroll.id',
                    'user_payroll.user_name',
                    'user_payroll.payrole_name',
                    'user_payroll.min_gaji',
                    'user_payroll.gaji_pokok',
                    'user_payroll.min_bonus',
                    'user_payroll.persentase_bonus'
                )
        )

            ->columns([
                TextColumn::make('user_name')->label('Nama User'),
                TextColumn::make('payrole_name')->label('Posisi'),
                TextColumn::make('total_unit')->label('Total Unit'),
                TextColumn::make('total_jasa')->label('Total Jasa'),
                TextColumn::make('total_sparepart')->label('Total Sparepart'),
                TextColumn::make('pendapatan_rp')->label('Pendapatan')->money('IDR', locale: 'id_ID'),
                TextColumn::make('gaji_rp')->label('Gaji')->money('IDR', locale: 'id_ID'),
                TextColumn::make('bonus_rp')->label('Bonus')->money('IDR', locale: 'id_ID'),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        DatePicker::make('tanggal_awal')->label('Dari Tanggal')->default(now()->startOfMonth()),
                        DatePicker::make('tanggal_akhir')->label('Sampai Tanggal')->default(now()->endOfMonth()),
                    ])
                    ->query(
                        fn ($query, array $data): Builder => $query
                            ->when(
                                $data['tanggal_awal'] ?? false,
                                fn ($query) => $query->whereDate('payroll_jurnals.created_at', '>=', $data['tanggal_awal'])
                            )
                            ->when(
                                $data['tanggal_akhir'] ?? false,
                                fn ($query) => $query->whereDate('payroll_jurnals.created_at', '<=', $data['tanggal_akhir'])
                            )
                    ),
            ]);
    }
}
