<?php

namespace App\Models;

use App\Helpers\SparepartDetailUpdate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SparepartDSale extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded;

    public function updateSparepartSaleTotal()
    {
        $subTotal = self::where('sparepart_sale_id', $this->sparepart_sale_id)
            ->sum('harga_subtotal');

        SparepartSale::where('id', $this->sparepart_sale_id)
            ->update(['total' => $subTotal]);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->created_by = Auth::id();
            SparepartDetailUpdate::prepareSparepartData($model);
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
            SparepartDetailUpdate::prepareSparepartData($model);
        });

        static::saved(fn ($model) => $model->updateSparepartSaleTotal());
        static::deleted(fn ($model) => $model->updateSparepartSaleTotal());
        static::restored(fn ($model) => $model->updateSparepartSaleTotal());
    }

    public function sparepartSatuan(): HasMany
    {
        return $this->hasMany(SparepartSatuans::class)->with('sparepart');
    }
    
    public function sparepartSale()
    {
        return $this->belongsTo(SparepartSale::class, 'parent_id');
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }
}
