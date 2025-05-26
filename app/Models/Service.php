<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Service extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'service_m_category_id',
                'service_m_type_id',
                'name',
                'kode',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'harga_1',
                'harga_2',
                'harga_3',
                'harga_4',
                'estimasi_waktu_pengerjaan',
            ])
            ->logOnlyDirty()
            ->useLogName('service');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Service telah di{$eventName}";
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
    }

    public function serviceDServices()
    {
        return $this->hasMany(ServiceDServices::class);
    }

    public function serviceMCategory() : BelongsTo{
        return $this->belongsTo(ServiceMCategory::class);
    }

    public function serviceMType() : BelongsTo{
        return $this->belongsTo(ServiceMType::class);
    }
}
