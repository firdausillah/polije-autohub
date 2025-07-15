<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PemasukanPengeluaran extends Model
{
    public static function getData($startDate, $endDate)
    {

        // dd($endDate);
        $content_data = DB::table('jurnals')
            ->whereBetween('jurnals.tanggal_transaksi', [$startDate, $endDate])
            ->get()
            ->toArray();

        $data = array_merge($content_data);

        return collect($data);
    }

    public static function getLaporanByTanggal($accountId, $startDate, $endDate)
    {

        $saldo_awal = DB::table('jurnals')
            ->where('tanggal_transaksi', '<', $startDate)
            ->where('account_id', '=', $accountId)
            ->selectRaw("
                COALESCE(SUM(debit)-SUM(kredit), 0) AS saldo_awal
            ")
            ->first();


        $transaksi = DB::table('jurnals')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->orderBy('tanggal_transaksi')
            ->where('account_id', '=', $accountId)
            ->get([
                'id',
                'kode',
                'tanggal_transaksi',
                'account_id',
                'transaction_type',
                'account_name',
                'debit',
                'kredit',
            ])
            ->toArray();

        $saldo = $saldo_awal->saldo_awal ?? 0;

        foreach ($transaksi as &$row) {
            if ($row->debit != 0) {
                $saldo += $row->debit;
            } elseif ($row->kredit != 0) {
                $saldo -= $row->kredit;
            }
            $row->saldo = $saldo;
        }

        // dd($transaksi);
        return collect($transaksi);
    }

}
