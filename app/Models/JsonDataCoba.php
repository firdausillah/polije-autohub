<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Sushi\Sushi;
use Illuminate\Database\Eloquent\Model as BaseModel;

class JsonDataCoba extends BaseModel
{
    use Sushi;


    public static function applySearch(array $data): array
    {
        $query = (new static)->getRows(); // Ambil data dari getRows()

        if (!empty($data['sparepart_id'])) {
            $query = array_filter($query, function ($row) use ($data) {
                return $row['sparepart_id'] == $data['sparepart_id'];
            });
        }

        if (!empty($data['tanggal_awal']) && !empty($data['tanggal_akhir'])) {
            $query = array_filter($query, function ($row) use ($data) {
                return $row['tanggal_transaksi'] >= $data['tanggal_awal'] &&
                    $row['tanggal_transaksi'] <= $data['tanggal_akhir'];
            });
        }

        return array_values($query);
    }

    public function getRows(): array
    {
        $saldo_awal = DB::table('view_saldo_awal')->first();
        $transaksi = collect(DB::table('view_transaksi')->get()); // Konversi ke Collection

        // Hitung saldo berjalan
        $saldo = $saldo_awal->saldo_awal ?? 0;
        $transaksi = $transaksi->map(function ($row) use (&$saldo) {
            if ($row->movement_type === 'IN-PUR') {
                $saldo += $row->jumlah;
            } elseif ($row->movement_type === 'OUT-SAL') {
                $saldo -= $row->jumlah;
            }
            $row->saldo = $saldo; // Tambahkan saldo ke tiap transaksi
            return (array) $row; // Ubah ke array per elemen
        });
        dd($transaksi->toArray());

        return $transaksi->toArray(); // Konversi Collection ke array
    }

}
