<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartPendapatanPertahun extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan Pertahun';

    protected static ?int $sort = 50;

    // public static function canView(): bool
    // {
    //     return auth()->check() && auth()->user()->hasRole(['Admin']);
    // }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    public ?string $filter = null;

    public function mount(): void
    {
        parent::mount();

        $this->filter = now()->format('Y');
    }


    protected function getData(): array
    {
        $year = $this->filter ?? now()->year;

        $data = DB::select("
        SELECT
            m.month_number,
            COALESCE(SUM(
                CASE 
                    WHEN ss.debit = 0 THEN ss.kredit 
                    ELSE ss.debit 
                END
            ), 0) AS jumlah
        FROM (
            SELECT 1 AS month_number UNION
            SELECT 2 UNION
            SELECT 3 UNION
            SELECT 4 UNION
            SELECT 5 UNION
            SELECT 6 UNION
            SELECT 7 UNION
            SELECT 8 UNION
            SELECT 9 UNION
            SELECT 10 UNION
            SELECT 11 UNION
            SELECT 12
        ) m
        LEFT JOIN (
            SELECT
                jurnals.debit,
                jurnals.kredit,
                jurnals.tanggal_transaksi
            FROM jurnals
            LEFT JOIN accounts ON jurnals.account_id = accounts.id
            WHERE
                accounts.type IN ('Pendapatan', 'Pendapatan Lain-lain')
                AND YEAR(jurnals.tanggal_transaksi) = ?
        ) ss
            ON MONTH(ss.tanggal_transaksi) = m.month_number
        GROUP BY m.month_number
        ORDER BY m.month_number
    ", [$year]);

        $jumlah = collect($data)->pluck('jumlah')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan Tahun ' . $year,
                    'data'  => $jumlah,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        $startYear = 2025;
        $currentYear = now()->year;

        $filters = [];

        for ($year = $currentYear; $year >= $startYear; $year--) {
            $filters[(string) $year] = (string) $year;
        }

        return $filters;
    }

}
