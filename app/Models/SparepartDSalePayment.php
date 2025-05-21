<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SparepartDSalePayment extends Model
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
                'account_id',
                'name',
                'kode',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'account_name',
                'account_kode',
                'jumlah_bayar',
                'biaya_tambahan',
                'total_payable',
                'photo',
            ])
            ->logOnlyDirty()
            ->useLogName('sparepart_d_sale_payment');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "sparepart_d_sale_payment telah di{$eventName}";
    }



    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });
    }

    public function sparepartSale(): BelongsTo
    {
        return $this->belongsTo(SparepartSale::class)->with('sparepart');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->where('kode', 'like', '100%');
    }

    // public function sparepartSale()
    // {
    //     return $this->belongsTo(SparepartSale::class, 'parent_id')->with('sparepart');
    // }
    
}
