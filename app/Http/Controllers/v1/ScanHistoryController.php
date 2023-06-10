<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ScanHistoryCollection;
use App\Http\Resources\v1\ScanHistoryResource;
use App\Models\v1\ScanHistory;
use App\Services\HttpStatus;

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
        $scanHistory = auth()->user()->scan_history()->paginate()->withQueryString();

        return (new ScanHistoryCollection($scanHistory))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\v1\ScanHistory  $scanHistory
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
     * @param  \App\Models\v1\ScanHistory  $scanHistory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ScanHistory $scanHistory)
    {
        //
    }
}