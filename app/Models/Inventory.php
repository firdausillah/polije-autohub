<?php

namespace App\Models;

use Filament\Forms\Components\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inventory extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'id',
                'sparepart_id',
                'satuan_id',
                'transaksi_h_id',
                'transaksi_d_id',
                'name',
                'kode',
                'keterangan',
                'created_by',
                'updated_by',
                'deleted_at',
                'created_at',
                'updated_at',
                'transaksi_h_kode',
                'sparepart_name',
                'sparepart_kode',
                'satuan_terkecil_name',
                'satuan_terkecil_kode',
                'tanggal_transaksi',
                'jumlah_unit',
                'jumlah_konversi',
                'jumlah_terkecil',
                'harga_unit',
                'harga_terkecil',
                'harga_subtotal',
                'movement_type',
                'relation_name',
                'relation_nomor_telepon',
            ])
            ->logOnlyDirty()
            ->useLogName('inventory');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "inventory telah di{$eventName}";
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

    public function spareparts(): HasMany
    {
        return $this->hasMany(Sparepart::class, 'sparepart_id');
    }

    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class, 'sparepart_id');
    }

}
