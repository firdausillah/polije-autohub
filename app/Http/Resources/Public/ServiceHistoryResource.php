<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vehicle_id' => $this->vehicle_id,
            'kode' => $this->kode,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'is_approve' => $this->is_approve,
            'kepala_unit_name' => $this->kepala_unit_name,
            'customer_name' => $this->customer_name,
            'nomor_telepon' => $this->nomor_telepon,
            'keluhan' => $this->keluhan,
            'km_datang' => $this->km_datang,
            'service_status' => $this->service_status,
            'working_start' => $this->working_start,
            'working_end' => $this->working_end,
            'total_estimasi_waktu' => $this->total_estimasi_waktu,
            'harga_subtotal' => $this->harga_subtotal,
            'discount' => $this->discount,
            // 'pajak_total' => $this->pajak_total,
            // 'service_total' => $this->service_total,
            // 'sparepart_total' => $this->sparepart_total,
            // 'part_total' => $this->part_total,
            // 'part_jumlah' => $this->part_jumlah,
            // 'liquid_total' => $this->liquid_total,
            // 'liquid_jumlah' => $this->liquid_jumlah,
            'total' => $this->total,
            // 'discount_service_total' => $this->discount_service_total,
            // 'discount_sparepart_total' => $this->discount_sparepart_total,
            // 'discount_total' => $this->discount_total,
            // 'payment_change' => $this->payment_change,
            'invoice_file' => $this->invoice_file,
            // 'is_customer_umum' => $this->is_customer_umum,
            'customer_alamat' => $this->customer_alamat,
            'customer_email' => $this->customer_email,
            // 'signature_path' => $this->signature_path,
            // 'is_signed' => $this->is_signed,
            'antrian_ke' => $this->antrian_ke,
            // 'is_diantar' => $this->is_diantar,
            // 'arrived_at' => $this->arrived_at,
            // 'fuel_level' => $this->fuel_level,
            // 'vehicle_condition' => $this->vehicle_condition,
            // 'cleanliness' => $this->cleanliness,
        ];
    }
}
