<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sushi\Sushi;

class JsonData extends Model
{
    use Sushi;

    public static function getRows(): array
    {
        $sparepart_id = null;
        $tanggal_awal = null;
        $tanggal_akhir = null;

        if (!$sparepart_id || !$tanggal_awal || !$tanggal_akhir) {
            return [];
        }

        $tanggal_akhir = Carbon::parse($tanggal_akhir)->addDay()->toDateString();

        // Query saldo awal sebelum tanggal_awal
        $saldo_awal = DB::table('inventories')
            ->where('sparepart_id', $sparepart_id)
            ->where('tanggal_transaksi', '<', $tanggal_awal)
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
        // dd($saldo_awal)->toSql();

        // Query transaksi dalam rentang tanggal
        $transaksi = DB::table('inventories')
            ->where('sparepart_id', $sparepart_id)
            ->where('tanggal_transaksi', [$tanggal_awal, $tanggal_akhir])
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

        // Hitung saldo berjalan
        $saldo = $saldo_awal->saldo_awal ?? 0;

        foreach ($transaksi as &$row) {
            if (in_array($row->movement_type, ['IN-PUR', 'IN-ADJ'])) {
                $saldo += $row->jumlah;
            } elseif (in_array($row->movement_type, ['OUT-SAL', 'OUT-ADJ'])) {
                $saldo -= $row->jumlah;
            }
            $row->saldo = $saldo; // Tambahkan saldo ke tiap transaksi
        }
        // dd(json_encode($transaksi));
        return json_decode(json_encode($transaksi), true); // Ubah jadi array
    }
}
