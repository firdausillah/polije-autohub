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
            ['created_by'=> 1, 'name' => 'Pcs/Unit', 'kode' => 'pcs'],
            ['created_by'=> 1, 'name' => 'Box/Karton', 'kode' => 'box'],
            ['created_by'=> 1, 'name' => 'Kilogam', 'kode' => 'kg'],
            ['created_by'=> 1, 'name' => 'Liter', 'kode' => 'lt'],
        );

    }
}
