<?php

namespace App\Models;

use Filament\Forms\Components\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    use HasFactory;

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
    }

    public static function kartuStok()
    {
        return self::query()
            ->selectRaw("
            CONCAT(transaksi_h_kode, '-', transaksi_d_id) AS id, -- Tambahkan kolom unik sebagai id
            CONCAT(sparepart_name, ' - ', sparepart_kode) AS sparepart, -- Tambahkan kolom unik sebagai id
            transaksi_h_kode AS transaksi_kode,
            tanggal_transaksi,
            sparepart_id,
            sparepart_name,
            sparepart_kode,
            satuan_terkecil_name AS satuan,
            relation_name,
            movement_type,
            jumlah_terkecil,
            '' AS qty_masuk,
            '' AS qty_keluar,

            SUM(
                CASE 
                    WHEN movement_type = 'IN-PUR' THEN jumlah_terkecil
                    WHEN movement_type = 'OUT-SAL' THEN -jumlah_terkecil
                    ELSE 0 
                END
            ) OVER (PARTITION BY sparepart_id ORDER BY tanggal_transaksi, transaksi_h_id, transaksi_d_id) AS saldo
        ")
            // ->where('sparepart_id', $sparepart_id)
            ->orderBy('tanggal_transaksi')
            ->orderBy('transaksi_h_id')
            ->orderBy('transaksi_d_id');
    }


    public function sparepart(): BelongsTo
    {
        return $this->belongsTo(Sparepart::class, 'sparepart_id');
    }

    public function qtyMasuk(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->movement_type === 'IN-PUR' ? $this->jumlah_terkecil : 0
        );
    }

    public function qtyKeluar(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->movement_type === 'OUT-SAL' ? $this->jumlah_terkecil : 0
        );
    }

    public function saldo($sparepart_id){
        return $this->select(
            "
                SELECT
                    SUM(
                        CASE 
                            WHEN movement_type = 'IN-PUR' THEN jumlah_terkecil 
                            WHEN movement_type = 'OUT-SAL' THEN -jumlah_terkecil
                            ELSE 0 
                        END
                    ) OVER (PARTITION BY sparepart_id ORDER BY tanggal_transaksi, transaksi_h_id, transaksi_d_id) AS saldo
                FROM 
                    inventories
                WHERE sparepart_id = $sparepart_id
                ORDER BY 
                    sparepart_id, tanggal_transaksi, transaksi_h_id, transaksi_d_id"
        )->get();
    }
}
