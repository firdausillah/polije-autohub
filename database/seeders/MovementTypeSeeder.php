<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovementTypeSeeder extends Seeder
{
    use HasFactory;
    public function run(): void
    {
        $movementTypes = [
            ['created_by'=> 1, 'kode' => 'IN-PUR', 'type' => 'IN', 'name' => 'Pembelian (Purchase)'],//
            ['created_by'=> 1, 'kode' => 'IN-RET', 'type' => 'IN', 'name' => 'Retur dari Pelanggan (Sales Return)'],
            ['created_by'=> 1, 'kode' => 'IN-TRF', 'type' => 'IN', 'name' => 'Transfer Masuk (Inbound Transfer)'],
            ['created_by'=> 1, 'kode' => 'IN-ADJ', 'type' => 'IN', 'name' => 'Penyesuaian Stok (Adjustment In)'], //
            ['created_by'=> 1, 'kode' => 'OUT-SAL', 'type' => 'OUT', 'name' => 'Penjualan (Sales)'], //
            ['created_by'=> 1, 'kode' => 'OUT-RET', 'type' => 'OUT', 'name' => 'Retur ke Supplier (Purchase Return)'],
            ['created_by'=> 1, 'kode' => 'OUT-TRF', 'type' => 'OUT', 'name' => 'Transfer Keluar (Outbound Transfer)'],
            ['created_by'=> 1, 'kode' => 'OUT-ADJ', 'type' => 'OUT', 'name' => 'Penyesuaian Stok (Adjustment Out)'], //
            ['created_by'=> 1, 'kode' => 'INT-TRF', 'type' => 'INTERNAL', 'name' => 'Transfer Antar-Gudang (Internal Transfer)'],
            ['created_by'=> 1, 'kode' => 'INT-USE', 'type' => 'INTERNAL', 'name' => 'Pemakaian Bahan (Material Usage)'],
            ['created_by'=> 1, 'kode' => 'INT-PROD', 'type' => 'INTERNAL', 'name' => 'Produksi (Production Input/Output)'],
            ['created_by'=> 1, 'kode' => 'CORR-COUNT', 'type' => 'CORRECTION', 'name' => 'Koreksi Hasil Stock Opname'],
            ['created_by'=> 1, 'kode' => 'CORR-LOST', 'type' => 'CORRECTION', 'name' => 'Kehilangan/Stok Hilang'],
            ['created_by'=> 1, 'kode' => 'CORR-EXP', 'type' => 'CORRECTION', 'name' => 'Barang Kedaluwarsa'],
            ['created_by'=> 1, 'kode' => 'CORR-DMG', 'type' => 'CORRECTION', 'name' => 'Barang Rusak'],
        ];

        DB::table('movement_types')->insert($movementTypes);
    }
}
