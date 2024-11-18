<?php

namespace App\Http\Controllers\User;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = $user->notifications()->getQuery();

        $query
            ->where(function (Builder $q) {
                $q->whereJsonDoesntContain('data->important', true);
                $q->orWhereNotNull('read_at');
            })
            ->orderBy('read_at', 'asc');

        $stats = [
            'read' => $user->notifications()->read()->count(),
            'unread' => $user->notifications()->unread()->count(),
            'important' => $user->notifications()->unread()->whereJsonContains('data->important', true)->count(),
        ];

        $additional = [
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ];

        if ($request->boolean('statsOnly')) {
            $additional['stats'] = $stats;
        } else {
            $notifications = $query->paginate($request->input('limit', 30));
            $important = $user->notifications()->unread()->whereJsonContains('data->important', true)->get();

            if ($request->boolean('withStats')) {
                $additional['stats'] = $stats;
            }
            $additional['important'] = new NotificationCollection($important ?? []);
        }

        return (new NotificationCollection($notifications ?? []))->additional($additional);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->find($id)->delete();

        return (new NotificationCollection([]))->additional([
            'message' => __('Notification Deleted'),
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }
}
