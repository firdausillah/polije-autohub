<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $fillable = [
        'name',
        'kode',
        'is_original',
        'part_number',
    ];
}
