<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CashDFlow extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'cash_flow_id',
                'account_id',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'account_name',
                'account_kode',
                'jumlah',
                'photo',
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
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
