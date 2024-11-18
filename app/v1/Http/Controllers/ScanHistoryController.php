<?php

namespace V1\Http\Controllers;

use App\Models\ScanHistory;
use V1\Http\Resources\ScanHistoryCollection;
use V1\Http\Resources\ScanHistoryResource;
use V1\Services\HttpStatus;

class ScanHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get logged user's scan history
        $scanHistory = auth()->user()->scanHistory()->paginate()->withQueryString();

        return (new ScanHistoryCollection($scanHistory))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(ScanHistory $scanHistory)
    {
        // Check if logged user is the owner of the scan history
        if ($scanHistory->user_id != auth()->user('sanctum')->id || auth('sanctum')->user()->role == 'admin') {
            return response()->json([
                'message' => HttpStatus::message(HttpStatus::UNAUTHORIZED),
                'status' => 'error',
                'status_code' => HttpStatus::UNAUTHORIZED,
            ], HttpStatus::UNAUTHORIZED);
        }

        return (new ScanHistoryResource($scanHistory))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(ScanHistory $scanHistory)
    {
        //
    }
}
