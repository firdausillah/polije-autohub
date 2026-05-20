<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ServiceBookingRequests as ModelsServiceBookingRequests;
use Illuminate\Http\Request;

class ServiceBookingRequests extends Controller
{
    public function store(Request $request)
    {
        // VALIDATION
        $validated = $request->validate([
            'customer_name'       => 'required|string|max:100',
            'nomor_telepon'       => 'required|string|max:20',
            'registration_number' => 'required|string|max:20',
            'category'            => 'required|string|max:50',
            'brand'               => 'required|string|max:50',
            'type'                => 'required|string|max:50',
            'keluhan'             => 'required|string',
            // 'booking_date'        => 'required|date',
            // 'booking_time'        => 'required',
        ]);

        // STORE
        $booking = ModelsServiceBookingRequests::create($validated);

        return ApiResponse::success(
            'request service booking created successfully',
            [
                'id' => $booking->id
            ]
        );
    }
}
