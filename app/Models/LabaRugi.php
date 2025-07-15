<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class LabaRugi extends Model
{
    protected $guarded;

    public static function getPendapatan($startDate, $endDate)
    {
        $content_data = DB::table('jurnals')
            ->select('accounts.name', 'accounts.kode', DB::raw('SUM(CASE WHEN jurnals.debit = 0 THEN jurnals.kredit ELSE jurnals.debit END) as jumlah'))
            ->leftJoin('accounts', 'jurnals.account_id', '=', 'accounts.id')
            ->whereIn('accounts.type', ['Pendapatan', 'Pendapatan Lain-lain'])
            ->whereBetween('jurnals.tanggal_transaksi', [$startDate, $endDate])
            ->groupBy('accounts.id', 'accounts.name', 'accounts.kode')
            ->get()
            ->toArray();
        
        $data = array_merge($content_data);

        return collect($data);
    }

    public static function getTotalPendapatan($startDate, $endDate)
    {
        $data = DB::table('jurnals')
        ->select(DB::raw("'Total' as name"), DB::raw("'' as kode"), DB::raw('SUM(CASE WHEN jurnals.debit = 0 THEN jurnals.kredit ELSE jurnals.debit END) as jumlah'))
        ->leftJoin('accounts', 'jurnals.account_id', '=', 'accounts.id')
        ->whereIn('accounts.type', ['Pendapatan', 'Pendapatan Lain-lain'])
        ->whereBetween('jurnals.tanggal_transaksi', [$startDate, $endDate])
            ->get()
            ->toArray();
            
        return $data;
    }

    public static function getHpp($startDate, $endDate)
    {
        $data = DB::table('jurnals')
            ->select('accounts.name', 'accounts.kode', DB::raw('SUM(debit) as jumlah'))
            ->leftJoin('accounts', 'jurnals.account_id', '=', 'accounts.id')
            ->where('accounts.kode', '5000')
            ->whereBetween('jurnals.tanggal_transaksi', [$startDate, $endDate])
            ->groupBy('accounts.id', 'accounts.name', 'accounts.kode')
            ->get()
            ->toArray();
            
        return collect($data);
    }

    public static function getLabaKotor($startDate, $endDate)
    {
        $total_pendapatan = self::getTotalPendapatan($startDate, $endDate)[0]->jumlah ?? '0';
        $total_hpp        = self::getHpp($startDate, $endDate)->toArray()[0]->jumlah ?? '0';

        $laba_kotor = (float)$total_pendapatan - (float)$total_hpp;

        return collect([
            [
                'name'   => 'Laba Kotor',
                'kode'   => '',
                'jumlah' => $laba_kotor
            ]
        ]);
    }

    public static function getBebanOperasional($startDate, $endDate)
    {
        $content_data = DB::table('jurnals')
        ->select('accounts.name', 'accounts.kode', DB::raw('SUM(CASE WHEN jurnals.debit = 0 THEN jurnals.kredit ELSE jurnals.debit END) as jumlah'))
        ->leftJoin('accounts', 'jurnals.account_id', '=', 'accounts.id')
        ->whereIN('accounts.type', ['Beban', 'Beban Lain-lain'])
        ->where('accounts.kode', '!=', '5000')
        ->whereBetween('jurnals.tanggal_transaksi', [$startDate, $endDate])
            ->groupBy('accounts.id', 'accounts.name', 'accounts.kode')
            ->get()
            ->toArray();

        $data = array_merge($content_data);

        return collect($data);
    }

    public static function getTotalBebanOperasional($startDate, $endDate)
    {
        $data = DB::table('jurnals')
        ->select(DB::raw("'Total' as name"), DB::raw("'' as kode"), DB::raw('SUM(CASE WHEN jurnals.debit = 0 THEN jurnals.kredit ELSE jurnals.debit END) as jumlah'))
        ->leftJoin('accounts', 'jurnals.account_id', '=', 'accounts.id')
        ->whereIn('accounts.type', ['Beban', 'Beban Lain-lain'])
        ->where('accounts.kode', '!=', '5000')
        ->whereBetween('jurnals.tanggal_transaksi', [$startDate, $endDate])
            ->get()
            ->toArray();

        return $data;
    }

    public static function getLabaOperasional($startDate, $endDate)
    {
        $total_laba_kotor = self::getLabaKotor($startDate, $endDate)->toArray()[0]['jumlah'] ?? '0';
        $total_total_beban_operasional        = self::getTotalBebanOperasional($startDate, $endDate)[0]->jumlah ?? '0';

        $laba_operasional = (float)$total_laba_kotor - (float)$total_total_beban_operasional;

        return collect([
            [
                'name'   => 'Laba Bersih',
                'kode'   => '',
                'jumlah' => $laba_operasional
            ]
        ]);
    }


}
