<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Sushi\Sushi;
use Illuminate\Database\Eloquent\Model as BaseModel;

class JsonDataCoba extends BaseModel
{
    public static function getLaporanByTanggal($startDate, $endDate)
    {
        return DB::select("
            SELECT id, kode, debit, kredit 
            FROM jurnals 
            WHERE created_at BETWEEN ? AND ?
        ", [$startDate, $endDate]);
    }
}
