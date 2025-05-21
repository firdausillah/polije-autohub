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
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SparepartDPurchase extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'sparepart_purchase_id',
                'sparepart_id',
                'satuan_id',
                'satuan_terkecil_id',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'sparepart_name',
                'sparepart_kode',
                'satuan_name',
                'satuan_kode',
                'satuan_terkecil_name',
                'satuan_terkecil_kode',
                'jumlah_unit',
                'jumlah_konversi',
                'jumlah_terkecil',
                'harga_unit',
                'harga_terkecil',
                'harga_subtotal',
            ])
            ->logOnlyDirty()
            ->useLogName('sparepart_d_purchase');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "sparepart_d_purchase telah di{$eventName}";
    }


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
