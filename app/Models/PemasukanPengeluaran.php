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
            ->whereBetween('jurnals.created_at', [$startDate, $endDate])
            ->get()
            ->toArray();

        $data = array_merge($content_data);

        return collect($data);
    }

    public static function getLaporanByTanggal($accountId, $startDate, $endDate)
    {

        $saldo_awal = DB::table('jurnals')
            ->where('created_at', '<', $startDate)
            ->where('account_id', '=', $accountId)
            ->selectRaw("
                COALESCE(SUM(debit)-SUM(kredit), 0) AS saldo_awal
            ")
            ->first();


        $transaksi = DB::table('jurnals')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->where('account_id', '=', $accountId)
            ->get([
                'id',
                'kode',
                'created_at',
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
