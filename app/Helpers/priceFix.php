<?php

namespace App\Helpers;

use App\Models\Hpp;
use App\Models\Sparepart;
use App\Models\SparepartSatuans;
use Illuminate\Support\Facades\DB;

class priceFix
{
    public static function priceFixer($sparepart_id)
    {
        $sparepart = Sparepart::find($sparepart_id);
        $hpp = Hpp::latest()->where('sparepart_id', $sparepart_id)->first()->hpp;

        $new_harga = Round::roundToNearest(ceil($hpp + $hpp * ($sparepart->margin/100) + ($sparepart->is_pajak?$hpp*11/100:0)));

        SparepartSatuans::where('sparepart_id', $sparepart_id)->update(['harga' => DB::raw('jumlah_konversi * ' . $new_harga)]);
    }

}
