<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sushi\Sushi;

class JsonData extends Model
{
    use Sushi;


    // public static function getFilteredQuery()
    // {
    //     $filters = $this->filters ?? [];

    //     $sparepart_id = $filters['sparepart_id'] ?? null;
    //     $tanggal_awal = $filters['tanggal_awal'] ?? now()->startOfMonth()->toDateString();
    //     $tanggal_akhir = $filters['tanggal_akhir'] ?? now()->endOfMonth()->toDateString();

    //     $saldo_awal = DB::table('view_saldo_awal')
    //         ->where('sparepart_id', $sparepart_id)
    //         ->value('saldo_awal') ?? 0;

    //     $transaksi = DB::table('view_transaksi')
    //         ->where('sparepart_id', $sparepart_id)
    //         ->whereBetween('tanggal_transaksi', [$tanggal_awal, $tanggal_akhir])
    //         ->get()
    //         ->toArray();

    //     // Hitung saldo berjalan
    //     $saldo = $saldo_awal;
    //     foreach ($transaksi as &$row) {
    //         if ($row->movement_type === 'IN-PUR') {
    //             $saldo += $row->jumlah;
    //         } elseif ($row->movement_type === 'OUT-SAL') {
    //             $saldo -= $row->jumlah;
    //         }
    //         $row->saldo = $saldo;
    //     }

    //     return collect($transaksi);
    // }

    // protected static $filters = [
    //     'sparepart_id' => null,
    //     'tanggal_awal' => null,
    //     'tanggal_akhir' => null,
    // ];

    // protected static ?string $sparepart_id = null;
    // protected static ?string $tanggal_awal = null;
    // protected static ?string $tanggal_akhir = null;

    public static function getFilteredQuery()
    {
        // return self::query();
        // ->when(self::$sparepart_id, fn ($query) => $query->where('sparepart_id', self::$sparepart_id))
        // ->when(self::$tanggal_awal, fn ($query) => $query->whereDate('tanggal_transaksi', '>=', self::$tanggal_awal))
        // ->when(self::$tanggal_akhir, fn ($query) => $query->whereDate('tanggal_transaksi', '<=', self::$tanggal_akhir));
    }

    public static function getRows(): array
    {
        // dd(static::$filters['tanggal_awal']);
        // Ambil filter dari static variable
        $sparepart_id = null;
        $tanggal_awal = null;
        $tanggal_akhir = null;
        // $sparepart_id = static::$filters['sparepart_id'];
        // $tanggal_awal = static::$filters['tanggal_awal'];
        // $tanggal_akhir = static::$filters['tanggal_akhir'];

        if (!$sparepart_id || !$tanggal_awal || !$tanggal_akhir) {
            return []; // Jika filter kosong, return array kosong
        }

        // Query saldo awal sebelum tanggal_awal
        $saldo_awal = DB::table('inventories')
            ->where('sparepart_id', $sparepart_id)
            ->where('tanggal_transaksi', '<', $tanggal_awal)
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
        // dd($saldo_awal)->toSql();

        // Query transaksi dalam rentang tanggal
        $transaksi = DB::table('inventories')
            ->where('sparepart_id', $sparepart_id)
            ->whereBetween('tanggal_transaksi', [$tanggal_awal, $tanggal_akhir])
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
                DB::raw("IF(movement_type = 'IN-PUR', jumlah_terkecil, 0) AS qty_masuk"),
                DB::raw("IF(movement_type = 'OUT-SAL', jumlah_terkecil, 0) AS qty_keluar"),
                'jumlah_terkecil AS jumlah'
            ])
            ->toArray();

        // Hitung saldo berjalan
        $saldo = $saldo_awal->saldo_awal ?? 0;
        foreach ($transaksi as &$row) {
            if ($row->movement_type === 'IN-PUR') {
                $saldo += $row->jumlah;
            } elseif ($row->movement_type === 'OUT-SAL') {
                $saldo -= $row->jumlah;
            }
            $row->saldo = $saldo; // Tambahkan saldo ke tiap transaksi
        }
        // dd(json_encode($transaksi));
        return json_decode(json_encode($transaksi), true); // Ubah jadi array
    }
}
