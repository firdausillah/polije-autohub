<?php

namespace App\Helpers;

use App\Models\Modal;
use App\Models\ServiceDServices;
use App\Models\ServiceDSparepart;
use App\Models\ServiceSchedule;
use App\Models\Sparepart;
use App\Models\SparepartSatuans;
use Illuminate\Support\Facades\DB;

class updateServiceTotal
{
    public static function updateTotal($service_schedule_id)
    {
        $estimasi_waktu_pengerjaan = ServiceDServices::where('service_schedule_id', $service_schedule_id)
            ->sum('estimasi_waktu_pengerjaan');

        $totalPajak = ServiceDSparepart::where('service_schedule_id', $service_schedule_id)
            ->sum('pajak');

        $serviceTotal = ServiceDServices::where('service_schedule_id', $service_schedule_id)
            ->selectRaw('SUM(harga_subtotal) as harga_subtotal, SUM(discount) as discount_total')
            ->first();

        $sparepartTotal = ServiceDSparepart::where('service_schedule_id', $service_schedule_id)
            ->selectRaw('SUM(harga_subtotal) as harga_subtotal, SUM(discount) as discount_total')
            ->first();

        $subTotal = $serviceTotal->harga_subtotal + $sparepartTotal->harga_subtotal;
        $discount_total = $serviceTotal->discount_total + $sparepartTotal->discount_total;

        ServiceSchedule::find($service_schedule_id)
            ->update([
                'service_total' => $serviceTotal->harga_subtotal,
                'sparepart_total' => $sparepartTotal->harga_subtotal,
                
                'discount_service_total' => $serviceTotal->discount_total,
                'discount_sparepart_total' => $sparepartTotal->discount_total,
                'discount_total' => $discount_total,
                
                'total_estimasi_waktu' => $estimasi_waktu_pengerjaan,
                'harga_subtotal' => $subTotal,
                'pajak_total' => $totalPajak,
                'total' => $subTotal - $discount_total
            ]);
    }

}
