<?php

namespace App\Models;

use App\Helpers\priceFix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SparepartSatuans extends Model
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

        static::saved(function ($model) {
            // update harga sparepart
            priceFix::priceFixer($model->sparepart_id);
        });
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class);
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }
}
