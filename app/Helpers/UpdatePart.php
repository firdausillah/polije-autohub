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

        $services = ServiceSchedule::where(['is_approve' => 'approved'])
        ->with([
            'kepalaUnit',
            'adminUser' => fn ($q) => $q->select('id', 'name'),
            'serviceDServices',
            'mekanikList.user:id,name'
        ])
        ->get();

        // cache user lookup (avoid repetitive find)
        $userCache = User::select('id', 'name')->get()->keyBy('id');

        foreach ($services as $record) {

            // Payroll
            $jumlah_service_terlayani = $record->serviceDServices->sum('jumlah');

            // admin + kepala unit payroll data
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


            // === ADMIN + KEPALA UNIT PAYROLL ===
            $adminUser = $userCache[$record->approved_by] ?? null;
            $kepalaUnit = $record->kepalaUnit;

            foreach ($adminJurnalData as $item) {
                if (!$item['check']) continue;

                // ADMIN
                if ($adminUser) {
                    self::createPayrollJurnal(array_merge(
                        [
                            'transaksi_h_id' => $record->id,
                            'kepala_unit_id' => $record->kepala_unit_id,
                            'user_id' => $adminUser->id,
                            'name' => $adminUser->name,
                            'keterangan' => 'Admin',
                            'created_at' => $record->approved_at,
                            'transaction_type' => 'Pelayanan Service',
                        ],
                        $item['extra']
                    ));
                }

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
            foreach ($record->mekanikList as $mekanik) {

                $mekanikUser = $mekanik->user;
                if (!$mekanikUser) continue;

                $mekanikPercentage = ($mekanik->mekanik_percentage / 100);

                $mekanikData = [
                    [
                        'check' => $record->service_total > 0,
                        'extra' => [
                            'jumlah_service' => $jumlah_service_terlayani,
                            'nominal' => max(0, $mekanikPercentage * $record->service_total),
                            'jenis_pendapatan' => 'service',
                        ],
                    ],
                    [
                        'check' => $record->liquid_total > 0,
                        'extra' => [
                            'jumlah_sparepart' => $record->liquid_jumlah,
                            'nominal' => max(0, $mekanikPercentage * $record->liquid_total),
                            'is_liquid' => 1,
                            'jenis_pendapatan' => 'liquid',
                        ],
                    ],
                    [
                        'check' => $record->part_total > 0,
                        'extra' => [
                            'jumlah_sparepart' => $record->part_jumlah,
                            'nominal' => max(0, $mekanikPercentage * $record->part_total),
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

        $sales = SparepartSale::where(['is_approve' => 'approved'])
        ->with([
            'kepalaUnit:id,name',
            'mekanik:id,name',
            'adminUser:id,name' // optional if relation exists
        ])
        ->get();

        // cache name lookup supaya aman jika relasi tidak ada
        $userCache = User::select('id', 'name')->get()->keyBy('id');

        foreach ($sales as $record) {

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
            $adminUser = $userCache[$record->approved_by] ?? null;
            $kepalaUnit = $record->kepalaUnit ?? ($record->kepala_unit_id ? ($userCache[$record->kepala_unit_id] ?? null) : null);

            foreach ($adminJurnalData as $item) {
                if (!$item['check']) continue;

                // ADMIN
                if ($adminUser) {
                    self::createPayrollJurnal(array_merge(
                        [
                            'transaksi_h_id' => $record->id,
                            'kepala_unit_id' => $record->kepala_unit_id,
                            'user_id' => $adminUser->id,
                            'name' => $adminUser->name,
                            'keterangan' => 'Admin',
                            'created_at' => $record->approved_at,
                            'transaction_type' => 'Penjualan Sparepart',
                        ],
                        $item['extra']
                    ));
                }

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
            $mekanikUser = $record->mekanik_id
                ? ($record->mekanik ?? ($userCache[$record->mekanik_id] ?? null))
                : null;

            if ($mekanikUser) {

                $mekanikData = [
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

                foreach ($mekanikData as $item) {
                    if (!$item['check']) continue;

                    self::createPayrollJurnal(array_merge([
                        'transaksi_h_id' => $record->id,
                        'kepala_unit_id' => $record->kepala_unit_id,
                        'user_id' => $mekanikUser->id,
                        'name' => $mekanikUser->name,
                        'keterangan' => 'Mekanik',
                        'created_at' => $record->approved_at,
                        'transaction_type' => 'Penjualan Sparepart',
                    ], $item['extra']));
                }
            }
        }

    }
}
