<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class service extends Model
{
    protected $fillable = [
        'name',
        'kode',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at',
        'harga',
        'komisi_mekanik',
        'estimasi_waktu_pengerjaan'
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
}
