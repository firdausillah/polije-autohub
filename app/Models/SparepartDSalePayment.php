<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SparepartDSalePayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded;


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
