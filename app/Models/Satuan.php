<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $fillable = [
        'name',
        'kode',
        'is_satuan_terkecil'
    ];
}
