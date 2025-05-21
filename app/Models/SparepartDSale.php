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

class SparepartDSale extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'sparepart_sale_id',
                'sparepart_id',
                'satuan_id',
                'satuan_terkecil_id',
                'sparepart_satuan_id',
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
                'pajak',
                'discount',
            ])
            ->logOnlyDirty()
            ->useLogName('sparepart_d_sale');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "sparepart_d_sale telah di{$eventName}";
    }


    public function updateSparepartSaleTotal()
    {
        $subTotal = self::where('sparepart_sale_id', $this->sparepart_sale_id)
            ->sum('harga_subtotal');
        $discountTotal = self::where('sparepart_sale_id', $this->sparepart_sale_id)
            ->sum('discount');

        SparepartSale::where('id', $this->sparepart_sale_id)
            ->update(['total' => $subTotal - $discountTotal]);
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

    public function sparepartSatuan(): BelongsTo
    {
        return $this->belongsTo(SparepartSatuans::class)->with('sparepart');
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
