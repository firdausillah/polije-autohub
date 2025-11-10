<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceMType extends Model
{


    public function ServiceDService(): HasOne
    {
        return $this->hasOne(ServiceDServices::class);
    }
}
