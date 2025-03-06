<?php

namespace App\Models;

use App\Helpers\priceFix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Sparepart extends Model
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

        static::saved(function ($model){
            // update harga sparepart
            priceFix::priceFixer($model->id);
        });
    }

    public function sparepartSatuan(): HasMany
    {
        return $this->hasMany(SparepartSatuans::class);
    }

    public function hpps(): HasMany
    {
        return $this->hasMany(Hpp::class);
    }
}
