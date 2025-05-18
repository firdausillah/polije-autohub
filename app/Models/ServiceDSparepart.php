<?php

namespace App\Models;

use App\Helpers\SparepartDetailUpdate;
use App\Helpers\updateServiceTotal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ServiceDSparepart extends Model
{
    protected $guarded;


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

        static::saved(function ($model) {
            updateServiceTotal::updateTotal($model->service_schedule_id);
        });

        static::deleted(function ($model) {
            updateServiceTotal::updateTotal($model->service_schedule_id);
        });
    }

    public function sparepartMCategory(): BelongsTo
    {
        return $this->belongsTo(SparepartMCategory::class);
    }

    public function sparepartSatuan(): BelongsTo
    {
        return $this->BelongsTo(SparepartSatuans::class)->with('sparepart');
    }
}
