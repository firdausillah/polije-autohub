<?php

namespace App\Models;

use App\Helpers\SparepartDetailUpdate;
use App\Helpers\updateServiceTotal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceDSparepart extends Model
{
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'service_schedule_id',
                'sparepart_id',
                'satuan_id',
                'satuan_terkecil_id',
                'sparepart_satuan_id',
                'sparepart_m_category_id',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'sparepart_name',
                'sparepart_kode',
                'satuan_name',
                'satuan_kode',
                'satuan_terkecil_name',
                'satuan_terkecil_kode',
                'jumlah_unit',
                'jumlah_konversi',
                'jumlah_terkecil',
                'harga_unit',
                'harga_terkecil',
                'harga_subtotal',
                'total',
                'discount',
                'pajak',
                'checklist_hasil',
            ])
            ->logOnlyDirty()
            ->useLogName('service_d_sparepart');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "service_d_sparepart telah di{$eventName}";
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
