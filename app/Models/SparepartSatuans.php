<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SparepartSatuans extends Model
{
    use HasFactory;
    protected $fillable = [
        'satuan_id',
        'sparepart_id',
        'name',
        'kode',
        'harga',
        'is_satuan_terkecil',
        'konversi'
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


    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class);
    }
}
