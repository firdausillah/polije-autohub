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

class CashFlow extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'account_id',
                'kode',
                'keterangan',
                'created_by',
                'approved_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'is_approve',
                'account_type',
                'total',
                'account_name',
                'account_kode',
                'tanggal_transaksi',
                'photo'
            ])
            ->logOnlyDirty()
            ->useLogName('cashflow');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Cashflow telah di{$eventName}";
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

        static::deleting(function ($model) {
            $model->cashDFlow()->delete();
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->whereIn('kode', [1001, 1002]);
    }

    public function cashDFlow(): HasMany
    {
        return $this->hasMany(CashDFlow::class);
    }
}
