<?php

namespace App\Helpers;

use App\Models\Modal;
use App\Models\PayrollJurnal;
use App\Models\ServiceDMekanik;
use App\Models\ServiceDServices;
use App\Models\ServiceDSparepart;
use App\Models\ServiceSchedule;
use App\Models\Sparepart;
use App\Models\SparepartSale;
use App\Models\SparepartSatuans;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdatePart
{

    public static function createPayrollJurnal($data)
    {
        PayrollJurnal::create($data);
    }
    
    public static function Service()
    {

        $services = ServiceSchedule::where(['is_approve' => 'approved'])->get();
        
        foreach ($services as $key => $record) {

            // Payroll
            $jumlah_service_terlayani   = ServiceDServices::where('service_schedule_id', $record->id)->sum('jumlah');

            // data jenis payroll admin
            $adminJurnalData = [
                [
                    'check' => $record->service_total > 0,
                    'extra' => [
                        'jumlah_service' => $jumlah_service_terlayani,
                        'nominal' => max(0, $record->service_total),
                        'jenis_pendapatan' => 'service',
                    ],
                ],
                [
                    'check' => $record->liquid_total > 0,
                    'extra' => [
                        'jumlah_sparepart' => $record->liquid_jumlah,
                        'nominal' => max(0, $record->liquid_total),
                        'is_liquid' => 1,
                        'jenis_pendapatan' => 'liquid',
                    ],
                ],
                [
                    'check' => $record->part_total > 0,
                    'extra' => [
                        'jumlah_sparepart' => $record->part_jumlah,
                        'nominal' => max(0, $record->part_total),
                        'jenis_pendapatan' => 'part',
                    ],
                ],
            ];

            // === PAYROLL ADMIN & KEPALA UNIT ===
            $kepalaUnit = User::find($record->kepala_unit_id);
            foreach ($adminJurnalData as $item) {
                if (!$item['check']) continue;

                // ADMIN
                self::createPayrollJurnal(array_merge(
                    [
                        'transaksi_h_id' => $record->id,
                        'kepala_unit_id' => $record->kepala_unit_id,
                        'user_id' => $record->approved_by,
                        'name' => User::find($record->approved_by)->name,
                        'keterangan' => 'Admin',
                        'created_at' => $record->approved_at,
                        'transaction_type' => 'Pelayanan Service',
                    ],
                    $item['extra']
                ));

                // KEPALA UNIT
                if ($kepalaUnit) {
                    self::createPayrollJurnal(array_merge(
                        [
                            'transaksi_h_id' => $record->id,
                            'kepala_unit_id' => $record->kepala_unit_id,
                            'user_id' => $kepalaUnit->id,
                            'name' => $kepalaUnit->name,
                            'keterangan' => 'Kepala Unit',
                            'created_at' => $record->approved_at,
                            'transaction_type' => 'Pelayanan Service',
                        ],
                        $item['extra']
                    ));
                }
            }
    
            // === PAYROLL MEKANIK ===
            $mekanikList = ServiceDMekanik::where('service_schedule_id', $record->id)->get();
            foreach ($mekanikList as $mekanik) {
                $mekanikUser = User::find($mekanik->mekanik_id);
                if (!$mekanikUser) continue;
    
                $mekanikData = [
                    [
                        'check' => $record->service_total > 0,
                        'extra' => [
                            'jumlah_service' => $jumlah_service_terlayani,
                            'nominal' => max(0, ($mekanik->mekanik_percentage / 100) * $record->service_total),
                            'jenis_pendapatan' => 'service',
                        ],
                    ],
                    [
                        'check' => $record->liquid_total > 0,
                        'extra' => [
                            'jumlah_sparepart' => $record->liquid_jumlah,
                            'nominal' => max(0, ($mekanik->mekanik_percentage / 100) * $record->liquid_total),
                            'is_liquid' => 1,
                            'jenis_pendapatan' => 'liquid',
                        ],
                    ],
                    [
                        'check' => $record->part_total > 0,
                        'extra' => [
                            'jumlah_sparepart' => $record->part_jumlah,
                            'nominal' => max(0, ($mekanik->mekanik_percentage / 100) * $record->part_total),
                            'jenis_pendapatan' => 'part',
                        ],
                    ],
                ];
    
                foreach ($mekanikData as $item) {
                    if (!$item['check']) continue;
    
                    self::createPayrollJurnal(array_merge([
                        'transaksi_h_id' => $record->id,
                        'kepala_unit_id' => $record->kepala_unit_id,
                        'user_id' => $mekanikUser->id,
                        'name' => $mekanikUser->name,
                        'keterangan' => 'Mekanik',
                        'created_at' => $record->approved_at,
                        'updated_at' => $record->approved_at,
                        'transaction_type' => 'Pelayanan Service',
                    ], $item['extra']));
                }
            }
        }

    }

    public static function Sale()
    {

        $sales = SparepartSale::where(['is_approve' => 'approved'])->get();
        foreach ($sales as $key => $record){

            // Payroll
            // data jenis payroll admin
            $adminJurnalData = [
                [
                    'check' => $record->liquid_total > 0,
                    'extra' => [
                        'jumlah_sparepart' => $record->liquid_jumlah,
                        'nominal' => max(0, $record->liquid_total),
                        'is_liquid' => 1,
                        'jenis_pendapatan' => 'liquid',
                    ],
                ],
                [
                    'check' => $record->part_total > 0,
                    'extra' => [
                        'jumlah_sparepart' => $record->part_jumlah,
                        'nominal' => max(0, $record->part_total),
                        'jenis_pendapatan' => 'part',
                    ],
                ],
            ];

            // === PAYROLL ADMIN & KEPALA UNIT ===
            $kepalaUnit = null;
            if ($record->kepala_unit_id != 0) {
                $kepalaUnit = User::find($record->kepala_unit_id);
            }
            foreach ($adminJurnalData as $item) {
                if (!$item['check']) continue;

                // ADMIN
                self::createPayrollJurnal(array_merge(
                    [
                        'transaksi_h_id' => $record->id,
                        'kepala_unit_id' => $record->kepala_unit_id,
                        'user_id' => $record->approved_by,
                        'name' => User::find($record->approved_by)->name,
                        'keterangan' => 'Admin',
                        'created_at' => $record->approved_at,
                        'transaction_type' => 'Penjualan Sparepart',
                    ],
                    $item['extra']
                ));

                // KEPALA UNIT
                if ($kepalaUnit) {
                    self::createPayrollJurnal(array_merge(
                        [
                            'transaksi_h_id' => $record->id,
                            'kepala_unit_id' => $record->kepala_unit_id,
                            'user_id' => $kepalaUnit->id,
                            'name' => $kepalaUnit->name,
                            'keterangan' => 'Kepala Unit',
                            'created_at' => $record->approved_at,
                            'transaction_type' => 'Penjualan Sparepart',
                        ],
                        $item['extra']
                    ));
                }
            }

            // === PAYROLL MEKANIK ===
            $mekanik = null;
            if ($record->mekanik_id != 0) {
                $mekanik = User::find($record->mekanik_id);
            }
            if ($mekanik) {

                $mekanikData = [
                    [
                        'check' => $record->liquid_total > 0,
                        'extra' => [
                            'jumlah_sparepart' => $record->liquid_jumlah,
                            'nominal' => $record->liquid_total,
                            'is_liquid' => 1,
                            'jenis_pendapatan' => 'liquid',
                        ],
                    ],
                    [
                        'check' => $record->part_total > 0,
                        'extra' => [
                            'jumlah_sparepart' => $record->part_jumlah,
                            'nominal' => $record->part_total,
                            'jenis_pendapatan' => 'part',
                        ],
                    ],
                ];

                foreach ($mekanikData as $item) {
                    if (!$item['check']) continue;

                    self::createPayrollJurnal(array_merge([
                        'transaksi_h_id' => $record->id,
                        'kepala_unit_id' => $record->kepala_unit_id,
                        'user_id' => $mekanik->id,
                        'name' => $mekanik->name,
                        'keterangan' => 'Mekanik',
                        'created_at' => $record->approved_at,
                        'transaction_type' => 'Penjualan Sparepart',
                    ], $item['extra']));
                }
            }
        }
    }
}
