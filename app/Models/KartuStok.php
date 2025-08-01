<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class KartuStok extends Model
{
    protected $guarded;


    public static function getLaporanByTanggal($sparepartId, $startDate, $endDate)
    {

        $endDate = Carbon::parse($endDate)->addDay()->toDateString();

        $saldo_awal = DB::table('inventories')
        ->where('sparepart_id', $sparepartId)
            ->where('tanggal_transaksi', '<', $startDate)
            ->selectRaw("
                COALESCE(SUM(
                    CASE 
                        WHEN movement_type IN ('IN-PUR', 'IN-ADJ') THEN jumlah_terkecil 
                        WHEN movement_type IN ('OUT-SAL', 'OUT-ADJ') THEN -jumlah_terkecil 
                        ELSE 0 
                    END
                ), 0) AS saldo_awal
            ")
            ->first();


        $transaksi = DB::table('inventories')
        ->where('sparepart_id', $sparepartId)
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->orderBy('tanggal_transaksi')
            ->orderBy('transaksi_h_kode')
            ->orderBy('transaksi_d_id')
            ->get([
                DB::raw("CONCAT(transaksi_h_kode, '-', transaksi_d_id) AS id"),
                DB::raw("CONCAT(sparepart_name, ' - ', sparepart_kode) AS sparepart"),
                'transaksi_h_kode AS transaksi_kode',
                'tanggal_transaksi',
                'sparepart_id',
                'sparepart_name',
                'sparepart_kode',
                'satuan_terkecil_name AS satuan',
                'relation_name',
                'movement_type',
                DB::raw("IF(movement_type IN ('IN-PUR', 'IN-ADJ'), jumlah_terkecil, 0) AS qty_masuk"),
                DB::raw("IF(movement_type IN ('OUT-SAL', 'OUT-ADJ'), jumlah_terkecil, 0) AS qty_keluar"),
                'jumlah_terkecil AS jumlah'
            ])
            ->toArray();

        $saldo = $saldo_awal->saldo_awal ?? 0;

        foreach ($transaksi as &$row) {
            if (in_array($row->movement_type, ['IN-PUR', 'IN-ADJ'])) {
                $saldo += $row->jumlah;
            } elseif (in_array($row->movement_type, ['OUT-SAL', 'OUT-ADJ'])) {
                $saldo -= $row->jumlah;
            }
            $row->saldo = $saldo; // Tambahkan saldo ke tiap transaksi
        }

        // dd($transaksi);
        return collect($transaksi);
    }
}
