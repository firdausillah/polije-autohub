<?php

namespace App\Filament\Widgets;

use App\Helpers\Round;
use App\Models\IncomeOverviews;
use App\Models\LabaRugi;
use App\Models\LastMonthIncomeComparison;
use App\Models\PayrollJurnal;
use App\Models\SalesReport;
use App\Models\VUserIncomeDaily;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class TotalStatsOverviewComparison extends BaseWidget
{
    protected static ?int $sort = 10;

    protected function getColumns(): int
    {
        return 2; // dua stat per baris (kiri & kanan)
    }

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['Kepala Unit']);
    }
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    protected function getStats(): array
    {
        // $pendapatan_per_bulan_lalu = IncomeOverviews::where(['id' => Auth::id()])->first()->total_perbandingan_persen;


        // $startLastMonth = Carbon::now()->startOfMonth()->subMonth();
        // $endLastMonth   = Carbon::now()->startOfMonth();
        // $startThisMonth = Carbon::now()->startOfMonth();
        // $endThisMonth   = Carbon::now()->startOfMonth()->addMonth();

        $dataBulanIni = VUserIncomeDaily::selectRaw('user_id, SUM(total_nominal) as nominal')
        ->whereBetween('tanggal', [
            now()->startOfMonth(),
            now()->startOfMonth()->addMonth()
        ])
            ->where('user_id', Auth::id())
            ->groupBy('user_id')
            ->first();

        $dataBulanLalu = VUserIncomeDaily::selectRaw('user_id, SUM(total_nominal) as nominal')
        ->whereBetween('tanggal', [
            now()->startOfMonth()->subMonth(),
            now()->startOfMonth()
        ])
            ->where('user_id', Auth::id())
            ->groupBy('user_id')
            ->first();

            // dd($dataBulanIni, $dataBulanLalu);

        // Mitigasi error
        $nominalBulanIni = $dataBulanIni->nominal ?? 0;
        $nominalBulanLalu = $dataBulanLalu->nominal ?? 0;


        // Cegah division by zero
        if ($nominalBulanLalu == 0) {
            $pendapatan_per_bulan = 0; // atau null, atau default lain
        } else {
            $pendapatan_per_bulan = Round((($nominalBulanIni / $nominalBulanLalu) * 100), 2);
        }

        $pendapatan_per_tanggal = optional(IncomeOverviews::where('id', Auth::id())->first()) ?? 0;

        $nominalTanggalIni = $pendapatan_per_tanggal->total_ini ?? 0;
        $nominalTanggalLalu = $pendapatan_per_tanggal->total_lalu ?? 0;

        // Cegah division by zero
        if ($nominalTanggalLalu == 0) {
            $pendapatan_per_tanggal_persen = 0; // atau null, atau default lain
        } else {
            $pendapatan_per_tanggal_persen = Round((($nominalTanggalIni / $nominalTanggalLalu) * 100), 2);
        }
        // dd($pendapatan_per_tanggal);

        // $pendapatan_per_tanggal = IncomeOverviews::where(['id' => Auth::id()])->first()->total_perbandingan_persen;

        return [

            Stat::make('Perbandingan Per Tanggal', 
                new HtmlString('
                    <div class="flex flex-row justify-between gap-4 items-center">
                        <div class="text-3xl font-bold">
                            '.$pendapatan_per_tanggal_persen . '%
                        </div>

                        <div class="text-sm text-gray-400">
                            <div>TM : Rp '.number_format($nominalTanggalIni, 0, ',', '.').'</div>
                            <div>LM : Rp '.number_format($nominalTanggalLalu, 0, ',', '.').'</div>
                        </div>
                    </div>
                ')
            )
            ->description(
                ($pendapatan_per_tanggal_persen >= 100 ? 'Naik ' : 'Turun ') . ($pendapatan_per_tanggal_persen - 100) . '%'
            )

            ->descriptionIcon($pendapatan_per_tanggal_persen >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($pendapatan_per_tanggal_persen >= 100 ? 'success' : 'danger'),

            Stat::make('Perbandingan Per Bulan',
                new HtmlString('
                    <div class="flex flex-row justify-between gap-4 items-center">
                        <div class="text-3xl font-bold">
                            '.$pendapatan_per_bulan . '%
                        </div>

                        <div class="text-sm text-gray-400">
                            <div>TM : Rp '.number_format($nominalBulanIni, 0, ',', '.').'</div>
                            <div>LM : Rp '.number_format($nominalBulanLalu, 0, ',', '.').'</div>
                        </div>
                    </div>
                ')
            )
            ->description(
                ($pendapatan_per_bulan >= 100 ? 'Naik ' : 'Turun ') . ($pendapatan_per_bulan - 100) . '%'
            )

            ->descriptionIcon($pendapatan_per_bulan >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($pendapatan_per_bulan >= 100 ? 'success' : 'danger')
        ];
    }
}
