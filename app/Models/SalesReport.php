<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SalesReport extends Model
{
    public static function getData($startDate, $endDate){
        $from = $startDate;
        $to = $endDate;

        // Format lengkap jam 00:00 dan 23:59
        $fromDate = $from . ' 00:00:00';
        $toDate = $to . ' 23:59:59';

        $results = DB::select("
            SELECT
                inventories.sparepart_id AS sparepart_id,
                inventories.sparepart_kode AS sparepart_kode,
                inventories.sparepart_name AS sparepart_name,
                sd.saldo,
                SUM(
                    IF(inventories.movement_type = 'OUT-SAL', inventories.jumlah_terkecil, 0)
                ) AS qty_terjual,
                SUM(
                    IF(inventories.movement_type = 'OUT-SAL', inventories.harga_subtotal, 0)
                ) AS total_penjualan
            FROM polije_autohub.inventories
            LEFT JOIN (
                SELECT 
                    inventories.sparepart_id,
                    COALESCE(
                        SUM(
                            CASE 
                                WHEN inventories.movement_type = 'IN-PUR' THEN inventories.jumlah_terkecil
                                WHEN inventories.movement_type = 'OUT-SAL' THEN -inventories.jumlah_terkecil
                                ELSE 0
                            END
                        ), 0
                    ) AS saldo
                FROM inventories 
                WHERE inventories.created_at <= ?
                GROUP BY inventories.sparepart_id
            ) AS sd ON inventories.sparepart_id = sd.sparepart_id 
            WHERE inventories.created_at BETWEEN ? AND ?
            GROUP BY inventories.sparepart_id, inventories.sparepart_kode , inventories.sparepart_name, sd.saldo
        ", [$endDate, $startDate, $endDate]);
        // dd($results);

        return $results;
    }
}
