<?php

namespace App\Http\Controllers\Api\Public;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\CustomerVehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerAuthController extends Controller
{
    public function login(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'access_code' => 'required|string'
        // ]);

        // if ($validator->fails()) {
        //     return ApiResponse::error('Invalid Access Code', 422);
        // }

        $accessCode = strtoupper(trim($request->access_code));

        $vehicle = Vehicle::where('access_code', $accessCode)->first();

        if (!$vehicle) {
            return ApiResponse::error('Invalid Access Code', 404);
        }

        $token = $vehicle->createToken('mobile')->plainTextToken;

        $vehicle->access_token = $token;

        return ApiResponse::success(
            new CustomerVehicleResource($vehicle),
            'Login success'
        );
    }

    public function logout(Request $request)
    {

        // dd($request->user()->currentAccessToken());
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(
            'Log out success'
        );
    }
}
