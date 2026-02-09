<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChartPendapatanPerbulan extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan Perbulan';

    protected static ?int $sort = 50;

    // public static function canView(): bool
    // {
    //     return auth()->check() && auth()->user()->hasRole(['Admin']);
    // }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }


    // protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {

        $data = DB::select("
                    SELECT
                        SUM(
                            CASE WHEN ss.debit = 0 THEN ss.kredit ELSE ss.debit
                        END
                    ) AS jumlah
                    FROM 
                    (
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
                    SELECT 12) m
                    left join 
                        (
                        SELECT
                            jurnals.id AS jurnal_id,
                            jurnals.debit,
                            jurnals.kredit,
                            jurnals.tanggal_transaksi
                        FROM
                            jurnals
                        LEFT JOIN accounts ON jurnals.account_id = accounts.id
                        WHERE
                            accounts.type IN(
                                'Pendapatan',
                                'Pendapatan Lain-lain'
                            ) AND YEAR(jurnals.tanggal_transaksi) = YEAR(CURDATE())) ss -- YEAR(CURDATE())) ss
                        on
                            MONTH(ss.tanggal_transaksi) = m.month_number
                            GROUP BY 
                    m.month_number
                    ORDER BY 
                    m.month_number
                ");
                
                foreach ($data as  $value) {
                    $jumlah[] = $value->jumlah;
                }
                // dd($jumlah);
            
        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $jumlah,
                    // 'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
