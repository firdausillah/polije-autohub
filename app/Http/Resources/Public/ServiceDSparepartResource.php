<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceDSparepartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'keterangan'    => $this->keterangan,
            'sparepart_name'    => $this->sparepart_name,
            'sparepart_kode'    => $this->sparepart_kode,
            'satuan_name'   => $this->satuan_name,
            'jumlah_unit'   => $this->jumlah_unit,
            'harga_unit'    => $this->harga_unit,
            'harga_subtotal'    => $this->harga_subtotal,
            'total' => $this->total,
            'discount'  => $this->discount,
            'pajak' => $this->pajak,
        ];
    }
}
