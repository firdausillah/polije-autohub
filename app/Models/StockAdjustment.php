<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'kode',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'tanggal_transaksi',
                'approved_by',
                'approved_at',
                'is_approve',
            ])
            ->logOnlyDirty()
            ->useLogName('sparepart_purchase');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "stock adjustment telah di{$eventName}";
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

    public function sparepartSatuan(): HasMany
    {
        return $this->hasMany(SparepartSatuans::class);
    }

    public function stockDAdjustment(): HasMany
    {
        return $this->hasMany(StockDAdjustment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

}
