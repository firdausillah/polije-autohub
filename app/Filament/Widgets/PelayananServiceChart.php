<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PelayananServiceChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Jumlah Pelanggan';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {

        $data = DB::select("
                    SELECT 
                    COALESCE(COUNT(ss.approved_at), 0) AS count
                    FROM 
                    (SELECT 1 AS month_number UNION
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
                    LEFT JOIN service_schedules ss
                        ON MONTH(ss.approved_at) = m.month_number
                    GROUP BY 
                    m.month_number
                    ORDER BY 
                    m.month_number
                ");

                foreach ($data as  $value) {
                    $jumlah[] = $value->count;
                }
            
        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pelanggan',
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
