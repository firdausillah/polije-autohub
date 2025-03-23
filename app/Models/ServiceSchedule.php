<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ServiceSchedule extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();

        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });

        static::created(function ($model){
            $checklists = Checklist::get();
            foreach ($checklists as $key => $value) {
                ServiceDChecklist::create([
                    'service_schedule_id' => $model->id,
                    'checklist_id' => $value->id,
                ]);
            }
        });
    }

    // Belongsto
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mekanik(): BelongsTo
    {
        return $this->belongsTo(UserRole::class)->where('role_name', 'like', 'Mekanik%');
    }

    public function kepalaMekanik(): BelongsTo
    {
        return $this->belongsTo(UserRole::class)->where('role_name', 'like', 'Kepala Mekanik%');
    }

    // HasMany
    public function serviceDChecklist(): HasMany
    {
        return $this->hasMany(ServiceDChecklist::class, 'service_schedule_id');
    }

    public function serviceDServices(): HasMany
    {
        return $this->hasMany(ServiceDServices::class);
    }

    public function serviceDSparepart(): HasMany
    {
        return $this->hasMany(ServiceDSparepart::class);
    }

    public function serviceDPayment(): HasMany
    {
        return $this->hasMany(ServiceDPayment::class);
    }
}
