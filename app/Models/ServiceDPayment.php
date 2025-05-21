<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceDPayment extends Model
{

    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'service_schedule_id',
                'account_id',
                'name',
                'kode',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'account_name',
                'account_kode',
                'jumlah_bayar',
                'biaya_tambahan',
                'total_payable',
                'payment_change',
                'photo',
            ])
            ->logOnlyDirty()
            ->useLogName('service_d_payment');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "service_d_payment telah di{$eventName}";
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
    
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->where('kode', 'like', '100%');
    }
}
