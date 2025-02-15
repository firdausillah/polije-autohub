<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class satuans extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Satuan::firstOrCreate(
            ['name' => 'Pcs/Unit', 'kode' => 'pcs'],
            ['name' => 'Box', 'kode' => 'box'],
            ['name' => 'Kilogam', 'kode' => 'kg'],
            ['name' => 'Liter', 'kode' => 'lt'],
        );

    }
}
