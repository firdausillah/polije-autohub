<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Sparepart extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'kode',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at',
        'is_original',
        'part_number',
        'komisi_admin'
    ];

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

    public function sparepartSatuan(): HasMany
    {
        return $this->hasMany(SparepartSatuans::class);
    }
}
