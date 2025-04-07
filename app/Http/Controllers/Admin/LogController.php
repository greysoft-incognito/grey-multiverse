<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\LogCollection;
use App\Http\Resources\LogResource;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        \App\Enums\Permission::LOGS_VIEW->authorize();
        $user = $request->user('sanctum');
        $supe = config('permission-defs.super-admin-role', 'super-admin');

        @[
            'action' => $action,
            'search' => $search,
        ] = $this->validate($request, [
            'action' => ['nullable', 'string', 'in:login,updated,created,deleted'],
            'search' => ['nullable', 'string'],
        ]);

        $query = Log::query()
            ->with(['loggable', 'user']);

        $query->when($search, function ($qx) use ($search) {
            $qx->whereHas('user', fn($q) => $q->doSearch($search));
        });

        $query->when(!$user->hasRole($supe), function ($qx) use ($supe) {
            $qx->whereDoesntHave(
                'user',
                fn($q) => $q->whereHas('roles', fn($q) => $q->whereName($supe))
            );
        });

        $query->when($action, fn($q) => $q->whereAction($action));

        $logs = $query->latest()->paginate($request->input('limit', 30));

        return (new LogCollection($logs))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }
}
