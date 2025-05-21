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

class SparepartPurchase extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'account_id',
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
                'supplier_name',
                'supplier_nomor_telepon',
                'is_approve',
                'sub_total',
                'discount',
                'total',
                'tanggal_transaksi',
                'purchase_receipt',
                'account_name',
                'account_kode',
                'photo',
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

    public function SparepartDPurchase(): HasMany
    {
        return $this->hasMany(SparepartDPurchase::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->where('kode', 'like', '100%');
    }
}
