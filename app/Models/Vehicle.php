<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Vehicle extends Model
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

    public function serviceHistories()
    {
        return $this->hasMany(ServiceSchedule::class)->where('service_schedules.is_approve', 'approved');
    }

    //perubahan ini
    public function serviceHistor()
    {
        return $this->hasOne(ServiceSchedule::class)->where('service_schedules.is_approve', 'approved')->latestOfMany(); 
    }
}
