<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payroll extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
            'id',
            'role_id',
            'service_m_category_id',
            'keterangan',
            'created_by',
            'updated_by',
            'deleted_at',
            'created_at',
            'updated_at',
            'gaji_pokok',
            'minimal_pendapatan_untuk_mendapat_gaji_pokok',
            'minimal_pendapatan_untuk_mendapat_bonus',
            'persentase_bonus',
            'sumber_pendapatan',
            ])
            ->logOnlyDirty()
            ->useLogName('payroll');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "payroll telah di{$eventName}";
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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
