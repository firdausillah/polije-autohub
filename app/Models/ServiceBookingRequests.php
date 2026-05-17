<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ServiceBookingRequests extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'customer_name',
        'nomor_telepon',
        'registration_number',
        'category',
        'brand',
        'type',
        'keluhan',
        'booking_date',
        'booking_time',
    ];
}
