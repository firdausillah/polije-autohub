<?php

namespace App\Helpers;

use App\Models\Sparepart;
use App\Models\SparepartSatuans;
use Exception;

class SparepartDetailUpdate
{
    /**
     * Mengisi data tambahan sebelum menyimpan atau mengupdate sparepart
     */
    public static function prepareSparepartData($sparepart_state)
    {
        $sparepart = Sparepart::where('id', $sparepart_state->sparepart_id)->first();
        $satuan = SparepartSatuans::where('id', $sparepart_state->satuan_id)->first();
        $satuan_terkecil = SparepartSatuans::where(['sparepart_id' => $sparepart_state->sparepart_id, 'is_satuan_terkecil' => 1])->first();
        if ($satuan_terkecil == null) {
            $satuan_terkecil = $satuan;
        }

        if (!$sparepart) {
            throw new Exception("Sparepart dengan kode {$sparepart->sparepart_kode} tidak ditemukan.");
        }

        if (!$satuan) {
            throw new Exception("Satuan dengan kode {$sparepart->satuan_kode} tidak ditemukan.");
        }

        $sparepart_state->sparepart_name = $sparepart->name;
        $sparepart_state->sparepart_kode = $sparepart->kode;

        $sparepart_state->satuan_name = $satuan->satuan_name;
        $sparepart_state->satuan_kode = $satuan->satuan_kode;
        
        $sparepart_state->satuan_terkecil_id = $satuan_terkecil->satuan_id;
        $sparepart_state->satuan_terkecil_name = $satuan_terkecil->satuan_name;
        $sparepart_state->satuan_terkecil_kode = $satuan_terkecil->satuan_kode;

        $sparepart_state->jumlah_konversi = $satuan->jumlah_konversi;
        $sparepart_state->jumlah_terkecil = $sparepart_state->jumlah_unit * $satuan->jumlah_konversi;

        $sparepart_state->harga_terkecil = $sparepart_state->harga_unit / $satuan->jumlah_konversi;
        $sparepart_state->harga_subtotal = $sparepart_state->harga_unit * $sparepart_state->jumlah_unit;
    }

    public static function prepareSparepartDataAdjustment($sparepart_state)
    {
        $sparepart = Sparepart::where('id', $sparepart_state->sparepart_id)->first();
        $satuan = SparepartSatuans::where('id', $sparepart_state->satuan_id)->first();
        $satuan_terkecil = SparepartSatuans::where(['sparepart_id' => $sparepart_state->sparepart_id, 'is_satuan_terkecil' => 1])->first();
        if ($satuan_terkecil == null) {
            $satuan_terkecil = $satuan;
        }

        if (!$sparepart) {
            throw new Exception("Sparepart dengan kode {$sparepart->sparepart_kode} tidak ditemukan.");
        }

        if (!$satuan) {
            throw new Exception("Satuan dengan kode {$sparepart->satuan_kode} tidak ditemukan.");
        }

        $sparepart_state->sparepart_name = $sparepart->name;
        $sparepart_state->sparepart_kode = $sparepart->kode;

        $sparepart_state->satuan_name = $satuan->satuan_name;
        $sparepart_state->satuan_kode = $satuan->satuan_kode;
        
        $sparepart_state->satuan_terkecil_id = $satuan_terkecil->satuan_id;
        $sparepart_state->satuan_terkecil_name = $satuan_terkecil->satuan_name;
        $sparepart_state->satuan_terkecil_kode = $satuan_terkecil->satuan_kode;

        $sparepart_state->jumlah_konversi = $satuan->jumlah_konversi;
        $sparepart_state->jumlah_terkecil = $sparepart_state->jumlah_unit * $satuan->jumlah_konversi;

        $sparepart_state->harga_terkecil = $sparepart_state->harga_unit / $satuan->jumlah_konversi;
        $sparepart_state->harga_subtotal = $sparepart_state->harga_unit * $sparepart_state->jumlah_terkecil_selisih;
    }
}
