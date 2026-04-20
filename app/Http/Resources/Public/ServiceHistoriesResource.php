<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceHistoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'vehicle_id' => $this->vehicle_id,
            'kode' => $this->kode,
            // 'created_by' => $this->created_by,
            // 'approved_by' => $this->approved_by,
            'admin_name' => $this->approvedBy?->name,
            'approved_at' => $this->approved_at,
            'is_approve' => $this->is_approve,
            'kepala_unit_name' => $this->kepala_unit_name,
            'customer_name' => $this->customer_name,
            'nomor_telepon' => $this->nomor_telepon,
            // 'keluhan' => $this->keluhan,
            // 'km_datang' => $this->km_datang,
            'service_status' => $this->service_status,
            'working_start' => $this->working_start,
            'working_end' => $this->working_end,
            'total_estimasi_waktu' => $this->total_estimasi_waktu,
            'harga_subtotal' => $this->harga_subtotal,
            'discount' => $this->discount,
            // 'total' => $this->total,
            'invoice_file' => $this->invoice_file,
            // 'customer_alamat' => $this->customer_alamat,
            // 'customer_email' => $this->customer_email,
            'antrian_ke' => $this->antrian_ke,
        ];
    }
}
