<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SalesReport extends Model
{
    public static function getData($sort_by, $startDate, $endDate){
        // dd($sort_by);

        switch ($sort_by) {
            case 'name_asc':
                $order_by = 'ORDER BY sparepart_name ASC';
                break;
            case 'name_desc':
                $order_by = 'ORDER BY sparepart_name DESC';
                break;
            case 'saldo_asc':
                $order_by = 'ORDER BY sd.saldo ASC';
                break;
            case 'saldo_desc':
                $order_by = 'ORDER BY sd.saldo DESC';
                break;
            case 'terjual_asc':
                $order_by = 'ORDER BY qty_terjual ASC';
                break;
            case 'terjual_desc':
                $order_by = 'ORDER BY qty_terjual DESC';
                break;
            case 'penjualan_asc':
                $order_by = 'ORDER BY total_penjualan ASC';
                break;
            case 'penjualan_desc':
                $order_by = 'ORDER BY total_penjualan DESC';
                break;
            default:
                $order_by = 'ORDER BY sparepart_id ASC';
                break;
        }

        $from = $startDate;
        $to = $endDate;

        // Format lengkap jam 00:00 dan 23:59
        $fromDate = $from . ' 00:00:00';
        $toDate = $to . ' 23:59:59';

        $results = DB::select("
            SELECT
                a.id AS sparepart_id,
                a.kode AS sparepart_kode,
                a.name AS sparepart_name,
                IFNULL(sd.saldo, 0) AS saldo,
                IFNULL(SUM(
                    IF(
                        b.movement_type = 'OUT-SAL',
                        b.jumlah_terkecil,
                        0
                    )
                ), 0) AS qty_terjual,
                IFNULL(SUM(
                    IF(
                        b.movement_type = 'OUT-SAL',
                        b.harga_subtotal,
                        0
                    )
                ), 0) AS total_penjualan
            FROM
                spareparts a
            LEFT JOIN inventories b 
                ON a.id = b.sparepart_id 
                AND b.created_at BETWEEN ? AND ?
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
                        ),
                        0
                    ) AS saldo
                FROM inventories
                WHERE inventories.created_at <= ?
                GROUP BY inventories.sparepart_id
            ) AS sd ON a.id = sd.sparepart_id
            GROUP BY a.id, a.kode, a.name, sd.saldo
            $order_by
        ", [$startDate, $endDate, $endDate]);
        // dd($results);

        return $results;
    }
}
