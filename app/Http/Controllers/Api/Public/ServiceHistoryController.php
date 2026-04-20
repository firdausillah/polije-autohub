<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ServiceHistoryResource;
use App\Models\ServiceSchedule;
use Illuminate\Http\Request;

class ServiceHistoryController extends Controller
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
    public function show()
    {
        $serviceCode = request()->query('kode');
        $serviceHistory = ServiceSchedule::with('serviceDServices.service_m_type', 'serviceDChecklist.checklist','serviceDSparepart','serviceDPayment')->where('kode', $serviceCode)->first();

        if (!$serviceHistory) {
            return ApiResponse::error('Service history not found', 404);
        }

        return ApiResponse::success(
            new ServiceHistoryResource($serviceHistory),
            'Service history retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
