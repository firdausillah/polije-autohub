<?php

namespace App\Models;

use App\Helpers\priceFix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;

class Modal extends Model
{

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'transaksi_h_id',
                'transaksi_d_id',
                'sparepart_id',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'tanggal_transaksi',
                'harga_modal',
                'transaksi_h_kode',
            ])
            ->logOnlyDirty()
            ->useLogName('modal');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "modal telah di{$eventName}";
    }

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

}
