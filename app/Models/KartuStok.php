<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class KartuStok extends Model
{
    protected $guarded;


    public static function getLaporanByTanggal($sparepartId, $startDate, $endDate)
    {

        $saldo_awal = DB::table('inventories')
        ->where('sparepart_id', $sparepartId)
            ->where('created_at', '<', $startDate)
            ->selectRaw("
                COALESCE(SUM(
                    CASE 
                        WHEN movement_type = 'IN-PUR' THEN jumlah_terkecil 
                        WHEN movement_type = 'OUT-SAL' THEN -jumlah_terkecil 
                        ELSE 0 
                    END
                ), 0) AS saldo_awal
            ")
            ->first();


        $transaksi = DB::table('inventories')
        ->where('sparepart_id', $sparepartId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->orderBy('transaksi_h_kode')
            ->orderBy('transaksi_d_id')
            ->get([
                DB::raw("CONCAT(transaksi_h_kode, '-', transaksi_d_id) AS id"),
                DB::raw("CONCAT(sparepart_name, ' - ', sparepart_kode) AS sparepart"),
                'transaksi_h_kode AS transaksi_kode',
                'created_at',
                'sparepart_id',
                'sparepart_name',
                'sparepart_kode',
                'satuan_terkecil_name AS satuan',
                'relation_name',
                'movement_type',
                DB::raw("IF(movement_type = 'IN-PUR', jumlah_terkecil, 0) AS qty_masuk"),
                DB::raw("IF(movement_type = 'OUT-SAL', jumlah_terkecil, 0) AS qty_keluar"),
                'jumlah_terkecil AS jumlah'
            ])
            ->toArray();

        $saldo = $saldo_awal->saldo_awal ?? 0;
        
        foreach ($transaksi as &$row) {
            if ($row->movement_type === 'IN-PUR') {
                $saldo += $row->jumlah;
            } elseif ($row->movement_type === 'OUT-SAL') {
                $saldo -= $row->jumlah;
            }
            $row->saldo = $saldo;
        }

        // dd($transaksi);
        return collect($transaksi);
    }
}
