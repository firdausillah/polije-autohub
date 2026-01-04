<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VUserincomeDaily extends Model
{
    public static function getIncomeComparisonReport(
        ?string $tanggalAkhir = null,
        ?array $userIds = null,
        ?array $jenisPendapatan = null,
        ?array $keterangan = null
        ): Collection {
        $tanggalAkhir = $tanggalAkhir ?? now()->toDateString();

        // Hitung awal bulan dan bulan lalu
        $awalBulanIni   = date('Y-m-01', strtotime($tanggalAkhir));
        $awalBulanLalu  = date('Y-m-01', strtotime('-1 month', strtotime($tanggalAkhir)));
        $akhirBulanLalu = date('Y-m-d', strtotime($awalBulanLalu . ' +' . (date('d', strtotime($tanggalAkhir)) - 1) . ' days'));

        // Base query untuk bulan ini
        $queryIni = DB::table('v_user_income_daily')
            ->select(
                'user_id',
                'user_name',
                'jenis_pendapatan',
                'keterangan',
                DB::raw('SUM(total_nominal) as total'),
                DB::raw("'INI' as periode")
            )
            ->whereBetween('tanggal', [$awalBulanIni, $tanggalAkhir])
            ->groupBy('user_id', 'user_name', 'jenis_pendapatan', 'keterangan');

        // Base query untuk bulan lalu
        $queryLalu = DB::table('v_user_income_daily')
            ->select(
                'user_id',
                'user_name',
                'jenis_pendapatan',
                'keterangan',
                DB::raw('SUM(total_nominal) as total'),
                DB::raw("'LALU' as periode")
            )
            ->whereBetween('tanggal', [$awalBulanLalu, $akhirBulanLalu])
            ->groupBy('user_id', 'user_name', 'jenis_pendapatan', 'keterangan');

        // Gabungkan kedua query menggunakan unionAll
        $incomeMtd = $queryIni->unionAll($queryLalu);
// dd($incomeMtd->get());
        // Buat query utama dari union
        $report = DB::table(DB::raw("({$incomeMtd->toSql()}) as income_mtd"))
            ->mergeBindings($incomeMtd)
            ->select(
                'user_id as id',
                'user_name as name',
                'keterangan',
                DB::raw("ROUND(IFNULL(MAX(CASE WHEN jenis_pendapatan='service' AND periode='INI' THEN total END) 
                                / NULLIF(MAX(CASE WHEN jenis_pendapatan='service' AND periode='LALU' THEN total END),0),0) * 100, 2) as service_perbandingan_persen"),
                DB::raw("ROUND(IFNULL(MAX(CASE WHEN jenis_pendapatan='part' AND periode='INI' THEN total END) 
                                / NULLIF(MAX(CASE WHEN jenis_pendapatan='part' AND periode='LALU' THEN total END),0),0) * 100, 2) as part_perbandingan_persen"),
                DB::raw("ROUND(IFNULL(MAX(CASE WHEN jenis_pendapatan='liquid' AND periode='INI' THEN total END) 
                                / NULLIF(MAX(CASE WHEN jenis_pendapatan='liquid' AND periode='LALU' THEN total END),0),0) * 100, 2) as liquid_perbandingan_persen"),
                DB::raw("ROUND(IFNULL(SUM(CASE WHEN periode='INI' THEN total END) 
                                / NULLIF(SUM(CASE WHEN periode='LALU' THEN total END),0),0) * 100, 2) as total_perbandingan_persen")
            )
            ->groupBy('user_id', 'user_name', 'keterangan');

        // Apply optional filters
        if ($userIds) {
            $report->whereIn('user_id', $userIds);
        }
        if ($jenisPendapatan) {
            $report->whereIn('jenis_pendapatan', $jenisPendapatan);
        }
        if ($keterangan) {
            $report->whereIn('keterangan', $keterangan);
        }

        // dd($report->get());

        return $report->get();
    }
}
