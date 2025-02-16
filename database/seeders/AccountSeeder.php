<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Aset (Assets)
            ['created_by'=> 1, 'kode' => '1001', 'name' => 'Kas', 'type' => 'Aset'],
            ['created_by'=> 1, 'kode' => '1002', 'name' => 'Bank', 'type' => 'Aset'],
            // ['created_by'=> 1, 'kode' => '1003', 'name' => 'Piutang Usaha', 'type' => 'Aset'],
            // ['created_by'=> 1, 'kode' => '1004', 'name' => 'Persediaan Sparepart', 'type' => 'Aset'],
            // ['created_by'=> 1, 'kode' => '1005', 'name' => 'Perlengkapan Bengkel', 'type' => 'Aset'],
            // ['created_by'=> 1, 'kode' => '1006', 'name' => 'Peralatan Bengkel', 'type' => 'Aset'],

            // Liabilitas (Liabilities)
            // ['created_by'=> 1, 'kode' => '2001', 'name' => 'Utang Usaha', 'type' => 'Liabilitas'],
            // ['created_by'=> 1, 'kode' => '2002', 'name' => 'Utang Bank', 'type' => 'Liabilitas'],
            // ['created_by'=> 1, 'kode' => '2003', 'name' => 'Utang Pajak', 'type' => 'Liabilitas'],

            // Ekuitas (Equity)
            ['created_by'=> 1, 'kode' => '3001', 'name' => 'Modal Pemilik', 'type' => 'Ekuitas'],
            // ['created_by'=> 1, 'kode' => '3002', 'name' => 'Prive', 'type' => 'Ekuitas'],

            // Pendapatan (Revenue)
            ['created_by'=> 1, 'kode' => '4001', 'name' => 'Pendapatan Jasa Servis', 'type' => 'Pendapatan'],
            ['created_by'=> 1, 'kode' => '4002', 'name' => 'Pendapatan Penjualan Sparepart', 'type' => 'Pendapatan'],
            ['created_by'=> 1, 'kode' => '4003', 'name' => 'Pendapatan Penjualan Produk', 'type' => 'Pendapatan'],

            // Beban (Expenses)
            ['created_by'=> 1, 'kode' => '5001', 'name' => 'Beban Bonus Admin', 'type' => 'Beban'],
            ['created_by'=> 1, 'kode' => '5002', 'name' => 'Beban Bonus Mekanik', 'type' => 'Beban'],
            ['created_by'=> 1, 'kode' => '5003', 'name' => 'Beban Gaji Admin', 'type' => 'Beban'],
            ['created_by'=> 1, 'kode' => '5004', 'name' => 'Beban Gaji Mekanik', 'type' => 'Beban'],
            ['created_by'=> 1, 'kode' => '5005', 'name' => 'Beban Pembelian Sparepart', 'type' => 'Beban'],
            ['created_by'=> 1, 'kode' => '5006', 'name' => 'Beban Sewa Bengkel', 'type' => 'Beban'],
            ['created_by'=> 1, 'kode' => '5007', 'name' => 'Beban Listrik, Air, dan Internet', 'type' => 'Beban'],
            ['created_by'=> 1, 'kode' => '5008', 'name' => 'Beban Penyusutan Peralatan', 'type' => 'Beban'],
            // ['created_by'=> 1, 'kode' => '5005', 'name' => 'Beban Penyusutan Peralatan', 'type' => 'Beban'],
            ['created_by'=> 1, 'kode' => '5009', 'name' => 'Beban Pajak', 'type' => 'Beban'],

            // Lain-lain (Other)
            ['created_by'=> 1, 'kode' => '6001', 'name' => 'Pendapatan Bunga', 'type' => 'Pendapatan Lain-lain'],
            ['created_by'=> 1, 'kode' => '6002', 'name' => 'Beban Denda atau Penalti', 'type' => 'Beban Lain-lain'],
        ];

        DB::table('accounts')->insert($accounts);
    }
}


// 4. Pencatatan dengan Debit dan Kredit
// Aset bertambah: Debit
// Aset berkurang: Kredit
// Liabilitas bertambah: Kredit
// Liabilitas berkurang: Debit
// Pendapatan: Kredit
// Beban: Debit
