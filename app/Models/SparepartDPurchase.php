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

class SparepartDPurchase extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded;

    public function updateSparepartPurchaseTotal()
    {
        $subTotal = self::where('sparepart_purchase_id', $this->sparepart_purchase_id)
            ->sum('harga_subtotal');

        SparepartPurchase::where('id', $this->sparepart_purchase_id)
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
        
        static::saved(fn ($model) => $model->updateSparepartPurchaseTotal());
        static::deleted(fn ($model) => $model->updateSparepartPurchaseTotal());
        static::restored(fn ($model) => $model->updateSparepartPurchaseTotal());
    }

    public function SparepartPurchase()
    {
        return $this->belongsTo(SparepartPurchase::class, 'parent_id');
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }
}
