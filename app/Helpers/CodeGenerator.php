<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class CodeGenerator
{
    public static function generateTransactionCode(string $prefix, string $table, string $column): string
    {
        $monthYear = now()->format('my');
        do {
            $lastRecord = DB::table($table)
                ->where($column, 'LIKE', "$prefix/$monthYear/%")
                ->latest($column)
                ->first();

            $nextNumber = $lastRecord
                ? ((int) substr($lastRecord->$column, -5)) + 1
                : 1;

            $newCode = sprintf('%s/%s/%05d', $prefix, $monthYear, $nextNumber);
        } while (DB::table($table)->where($column, $newCode)->exists());

        return $newCode;
    }

    public static function generateSimpleCode($prefix, $table, $field)
    {
        do {
            // Ambil nomor urut terakhir dari database
            $lastCode = DB::table($table)
                ->where($field, 'like', "$prefix%")
                ->orderBy($field, 'desc')
                ->value($field);

            // Ambil angka terakhir dari kode
            if ($lastCode) {
                $lastNumber = (int) substr($lastCode, strlen($prefix));
            } else {
                $lastNumber = 0;
            }

            // Tambah 1 untuk nomor urut baru
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            // Buat kode baru
            $newCode = $prefix . $newNumber;
        } while (DB::table($table)->where($field, $newCode)->exists());

        return $newCode;
    }


}
