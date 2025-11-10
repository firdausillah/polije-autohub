<?php

namespace App\Models;

use App\Helpers\updateServiceTotal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceDServices extends Model
{

    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'service_schedule_id',
                'service_id',
                'service_m_type_id',
                'name',
                'kode',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'service_name',
                'service_kode',
                'jumlah',
                'harga_unit',
                'harga_subtotal',
                'total',
                'discount',
                'is_approve',
                'estimasi_waktu_pengerjaan',
                'checklist_hasil',
            ])
            ->logOnlyDirty()
            ->useLogName('service_d_service');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "service_d_service telah di{$eventName}";
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

    public function service_m_type(): BelongsTo
    {
        return $this->belongsTo(ServiceMType::class, 'service_m_type_id');
    }
    
    // public function services(): HasMany
    // {
    //     return $this->hasMany(Service::class);
    // }
}
