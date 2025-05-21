<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Jurnal extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'transaksi_h_id',
                'transaksi_d_id',
                'account_id',
                'kode',
                'keterangan',
                'created_by',
                'created_at',
                'account_name',
                'account_kode',
                'tanggal_transaksi',
                'transaction_type',
                'relation_name',
                'relation_nomor_telepon',
                'debit',
                'kredit',
            ])
            ->logOnlyDirty()
            ->useLogName('jurnal');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "jurnal telah di{$eventName}";
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
    }
}
