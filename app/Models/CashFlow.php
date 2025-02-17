<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class CashFlow extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_debit_id',
        'account_kredit_id',
        'name',
        'kode',
        'keterangan',
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at',
        'transaksi_type_debit',
        'transaksi_type_kredit',
        'is_approve',
        'total',
        'account_debit_nama',
        'account_kredit_nama',
        'account_debit_kode',
        'account_kredit_kode'
    ];

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

    public function accounts(): HasOne
    {
        return $this->hasOne(Account::class);
    }
}
