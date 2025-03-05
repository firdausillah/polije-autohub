<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SparepartSale extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->tanggal_transaksi = NOW();
            $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    public function SparepartDSale(): HasMany
    {
        return $this->hasMany(SparepartDSale::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->where('type', 'Aset');
    }
}
