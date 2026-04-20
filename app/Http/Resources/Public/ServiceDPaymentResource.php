<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceDPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'account_name'  => $this->account_name,
            // 'account_kode'  => $this->account_kode,
            'jumlah_bayar'  => $this->jumlah_bayar,
            // 'biaya_tambahan'    => $this->biaya_tambahan,
            'total_payable' => $this->total_payable,
            'payment_change'    => $this->payment_change,
            'photo' => $this->photo,
        ];
    }
}
