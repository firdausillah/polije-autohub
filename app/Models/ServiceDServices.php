<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

class ServiceDServices extends Model
{

    protected $guarded;

    public function updateServiceTotal()
    {
        $estimasi_waktu_pengerjaan = ServiceDServices::where('service_schedule_id',$this->service_schedule_id)
            ->sum('estimasi_waktu_pengerjaan');

        $subTotalService = ServiceDServices::where('service_schedule_id',$this->service_schedule_id)
            ->sum('harga_subtotal');

        $subTotalSparepart = ServiceDSparepart::where('service_schedule_id',$this->service_schedule_id)
            ->sum('harga_subtotal');

        $subTotal = $subTotalService + $subTotalSparepart;

        ServiceSchedule::find($this->service_schedule_id)
            ->update(['total' =>$subTotal, 'service_total' => $subTotalService, 'sparepart_total' => $subTotalSparepart, 'total_estimasi_waktu' => $estimasi_waktu_pengerjaan]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });

        static::saved(fn ($model) => $model->updateServiceTotal());
        static::deleted(fn ($model) => $model->updateServiceTotal());
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function serviceMCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceMCategory::class);
    }

    public function serviceMType(): BelongsTo
    {
        return $this->belongsTo(ServiceMType::class)->where('service_m_category_id', Payroll::find(auth()->user()->payroll_id)->service_m_category_id);
    }
    
    // public function services(): HasMany
    // {
    //     return $this->hasMany(Service::class);
    // }
}
