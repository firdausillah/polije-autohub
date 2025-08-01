<?php

namespace App\Models;

use App\Helpers\SparepartDetailUpdate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockDAdjustment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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
            ->useLogName('stock_d_adjustment');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "stock_d_adjustment telah di{$eventName}";
    }

    public function updateStockAdjustmentTotal()
    {
        $subTotal = self::where('stock_adjustment_id', $this->stock_adjustment_id)
            ->sum('harga_subtotal');

        StockAdjustment::where('id', $this->stock_adjustment_id)
            ->update(['total' => $subTotal]);
    }


    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->created_by = Auth::id();
            SparepartDetailUpdate::prepareSparepartDataAdjustment($model);
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
            SparepartDetailUpdate::prepareSparepartDataAdjustment($model);
        });

        static::saved(fn ($model) => $model->updateStockAdjustmentTotal());
        static::deleted(fn ($model) => $model->updateStockAdjustmentTotal());
        static::restored(fn ($model) => $model->updateStockAdjustmentTotal());
    }

    public function sparepartSatuan(): BelongsTo
    {
        return $this->belongsTo(SparepartSatuans::class)->with('sparepart');
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class);
    }
}