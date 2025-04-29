<?php

namespace App\Helpers;

use App\Models\Modal;
use App\Models\Sparepart;
use App\Models\SparepartSatuans;
use Illuminate\Support\Facades\DB;

class priceFix
{
    public static function priceFixer($sparepart_id)
    {
        $sparepart = Sparepart::find($sparepart_id);
        $harga_modal = Modal::latest()->where('sparepart_id', $sparepart_id)->first()->harga_modal??0;

        $new_harga = Round::roundToNearest(ceil($harga_modal + $harga_modal * ($sparepart->margin/100) + ($sparepart->is_pajak? $harga_modal*11/100:0)));

        SparepartSatuans::where('sparepart_id', $sparepart_id)->update(['harga' => DB::raw('jumlah_konversi * ' . $new_harga)]);
    }

}
