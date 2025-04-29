<?php

namespace App\Models;

use App\Helpers\SparepartDetailUpdate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ServiceDSparepart extends Model
{
    protected $guarded;

    public function updateServiceTotal()
    {
        $estimasi_waktu_pengerjaan = ServiceDServices::where('service_schedule_id', $this->service_schedule_id)
            ->sum('estimasi_waktu_pengerjaan');

        $subTotalService = ServiceDServices::where('service_schedule_id', $this->service_schedule_id)
            ->sum('harga_subtotal');

        $subTotalSparepart = ServiceDSparepart::where('service_schedule_id', $this->service_schedule_id)
            ->sum('harga_subtotal');

        $totalPajak = ServiceDSparepart::where('service_schedule_id', $this->service_schedule_id)
            ->sum('pajak');

        $subTotal = $subTotalService + $subTotalSparepart;

        ServiceSchedule::find($this->service_schedule_id)
            ->update(['total' => $subTotal, 'service_total' => $subTotalService, 'sparepart_total' => $subTotalSparepart, 'total_estimasi_waktu' => $estimasi_waktu_pengerjaan, 'pajak_total' => $totalPajak]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();
            SparepartDetailUpdate::prepareSparepartData($model);
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
            SparepartDetailUpdate::prepareSparepartData($model);
        });

        static::saved(fn ($model) => $model->updateServiceTotal());
        static::deleted(fn ($model) => $model->updateServiceTotal());
    }


    public function sparepartSatuan(): BelongsTo
    {
        return $this->BelongsTo(SparepartSatuans::class)->with('sparepart');
    }
}
