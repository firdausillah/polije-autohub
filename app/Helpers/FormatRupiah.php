<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class FormatRupiah
{
    public static function rupiah($angka, $withDecimal = false)
    {
        $jumlahDesimal = $withDecimal ? 2 : 0;
        return 'Rp ' . number_format($angka, $jumlahDesimal, ',', '.');
    }


}
