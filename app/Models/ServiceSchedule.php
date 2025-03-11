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
            
            $checklists = Checklist::get();
            foreach ($checklists as $key => $value) {
                ServiceDChecklist::create([
                    'service_schedule_id' => $model->id,
                    'checklist_id' => $value->id,
                ]);
            }
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function mekanik(): BelongsTo
    {
        return $this->belongsTo(UserRole::class)->where('role_name', 'like', 'Mekanik%');
    }

    public function ServiceDChecklist(): HasMany
    {
        return $this->hasMany(ServiceDChecklist::class);
    }

    public function ServiceDServices(): HasMany
    {
        return $this->hasMany(ServiceDServices::class);
    }

    public function ServiceDSparepart(): HasMany
    {
        return $this->hasMany(ServiceDSparepart::class);
    }
}
