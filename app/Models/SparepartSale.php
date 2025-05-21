<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SparepartSale extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'name',
                'kode',
                'keterangan',
                'created_by',
                'updated_by',
                'approved_by',
                'approved_at',
                'deleted_at',
                'created_at',
                'updated_at',
                'customer_name',
                'customer_nomor_telepon',
                'is_approve',
                'sub_total',
                'discount_total',
                'pajak_total',
                'total',
                'payment_change',
                'tanggal_transaksi',
                'photo',
                'invoice_file',
            ])
            ->logOnlyDirty()
            ->useLogName('sparepart_purchase');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "sparepart_purchase telah di{$eventName}";
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->tanggal_transaksi = NOW();
            $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    public function sparepartDSale(): HasMany
    {
        return $this->hasMany(SparepartDSale::class);
    }

    public function sparepartDSalePayment(): HasMany
    {
        return $this->hasMany(SparepartDSalePayment::class);
    }
}
