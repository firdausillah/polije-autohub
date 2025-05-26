<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceDChecklist extends Model
{

    // use LogsActivity;

    protected $guarded = [];

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnly([
    //             'id',
    //             'service_schedule_id',
    //             'checklist_id',
    //             'keterangan',
    //             'created_by',
    //             'updated_by',
    //             'deleted_at',
    //             'created_at',
    //             'updated_at',
    //             'checklist_name',
    //             'checklist_hasil',
    //         ])
    //         ->logOnlyDirty()
    //         ->useLogName('service_d_checklist');
    // }

    // public function getDescriptionForEvent(string $eventName): string
    // {
    //     return "service_d_checklist telah di{$eventName}";
    // }
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

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }
}
