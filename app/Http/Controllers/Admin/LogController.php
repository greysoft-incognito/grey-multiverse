<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\LogResource;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        \App\Enums\Permission::LOGS_VIEW->authorize();

        $query = Log::query()
            ->with(['loggable', 'user']);

        $logs = $query->paginate($request->input('limit', 30));

        return LogResource::collection($logs)->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }
}
