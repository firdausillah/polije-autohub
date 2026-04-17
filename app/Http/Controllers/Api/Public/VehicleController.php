<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\VehicleResource;
use App\Models\ServiceSchedule;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $kode)
    {
        $vehicle = Vehicle::with('getServiceHistories')->where('kode', $kode)->first();

        if (!$vehicle) {
            return ApiResponse::error('Vehicle not found', 404);
        }

        return ApiResponse::success(
            new VehicleResource($vehicle),
            'Vehicle retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $kode)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $kode)
    {
        //
    }
}
