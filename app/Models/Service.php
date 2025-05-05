<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Service extends Model
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
    }

    public function serviceDServices()
    {
        return $this->hasMany(ServiceDServices::class);
    }

    public function serviceMCategory() : BelongsTo{
        return $this->belongsTo(ServiceMCategory::class);
    }

    public function serviceMType() : BelongsTo{
        return $this->belongsTo(ServiceMType::class);
    }
}
