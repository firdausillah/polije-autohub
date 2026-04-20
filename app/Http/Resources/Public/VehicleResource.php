<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'kode' => $this->kode,
            'registration_number' => $this->registration_number,
            'brand' => $this->brand,
            'type' => $this->type,
            'category' => $this->category,
            'model' => $this->model,
            'tahun_pembuatan' => $this->tahun_pembuatan,
            'cc' => $this->cc,
            'nomor_rangka' => $this->nomor_rangka,
            'nomor_mesin' => $this->nomor_mesin,
            'warna' => $this->warna,
            'bahan_bakar' => $this->bahan_bakar,
            'has_history' => $this->getServiceHistories->isNotEmpty(),

            'histories' => ServiceHistoriesResource::collection(
                $this->whenLoaded('getServiceHistories')
            ),
        ];
        // return parent::toArray($request);
    }
}
