<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceDServicesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'service_id'    => $this->service_id,
            'service_m_type_name' => $this->service_m_type?->name,
            'service_name'  => $this->service_name,
            'service_kode'  => $this->service_kode,
            'jumlah'    => $this->jumlah,
            'harga_unit'    => $this->harga_unit,
            'harga_subtotal'    => $this->harga_subtotal,
            'total' => $this->total,
            'discount'  => $this->discount,
            // 'is_approve'    => $this->is_approve,
            // 'estimasi_waktu_pengerjaan' => $this->estimasi_waktu_pengerjaan,
            // 'checklist_hasil'   => $this->checklist_hasil,
        ];
    }
}
