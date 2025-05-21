<?php

namespace App\Models;

use App\Helpers\priceFix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sparepart extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'sparepart_m_category_id',
                'name',
                'kode',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'is_original',
                'part_number',
                'margin',
                'is_pajak',
            ])
            ->logOnlyDirty()
            ->useLogName('sparepart');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Sparepart telah di{$eventName}";
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

        static::saved(function ($model){
            // update harga sparepart
            priceFix::priceFixer($model->id);
        });
    }

    public function sparepartSatuan(): HasMany
    {
        return $this->hasMany(SparepartSatuans::class);
    }

    public function modals(): HasMany
    {
        return $this->hasMany(Modal::class);
    }

    public function sparepartMCategory(): BelongsTo
    {
        return $this->belongsTo(SparepartMCategory::class);
    }
}
