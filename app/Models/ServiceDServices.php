<?php

namespace App\Models;

use App\Helpers\updateServiceTotal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

class ServiceDServices extends Model
{

    protected $guarded;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });

        static::saved(function ($model) {
            updateServiceTotal::updateTotal($model->service_schedule_id);
        });

        static::deleted(function ($model) {
            updateServiceTotal::updateTotal($model->service_schedule_id);
        });
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
