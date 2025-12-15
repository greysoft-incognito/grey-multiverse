<?php

namespace V1\Http\Controllers\Admin;

use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Http\Request;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\ReservationCollection;
use V1\Http\Resources\ReservationResource;
use V1\Services\HttpStatus;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Space $space)
    {
        $this->authorize('can-do', ['reservation.list']);
        $query = $space->reservations();
        // Search and filter columns
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%");
                $query->orWhereFullText('info', $request->search);
                $query->orWherePrice($request->search);
            });
        }

        // Reorder Columns
        if ($request->order && is_array($request->order)) {
            foreach ($request->order as $key => $dir) {
                if ($dir === 'desc') {
                    $query->orderByDesc($key ?? 'id');
                } else {
                    $query->orderBy($key ?? 'id');
                }
            }
        }

        $reservations = $query->paginate($request->get('limit', 30));

        return (new ReservationCollection($reservations))->additional(array_merge([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ], $request->search ? ['total_results' => $query->count()] : []));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request, $status = 'reserved')
    {
        $this->authorize('can-do', ['reservation.list']);
        $query = Reservation::query();

        if (in_array($status, ['reserved', 'paid', 'pending', 'cancelled', 'failed'])) {
            $query->whereHas('transactions', function ($query) use ($status) {
                if ($status === 'reserved') {
                    $query->where(function ($query) {
                        $query->where('status', 'pending');
                        $query->orWhere('status', 'paid');
                    });
                } else {
                    $query->where('status', $status);
                }
            });
        }

        // Search and filter columns
        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%");
                $query->orWhereFullText('info', $request->search);
                $query->orWherePrice($request->search);
            });
        }

        // Reorder Columns
        if ($request->order && is_array($request->order)) {
            foreach ($request->order as $key => $dir) {
                if ($dir === 'desc') {
                    $query->orderByDesc($key ?? 'id');
                } else {
                    $query->orderBy($key ?? 'id');
                }
            }
        }

        $reservations = $query->paginate($request->get('limit', 30));

        return (new ReservationCollection($reservations))->additional(array_merge([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ], $request->search ? ['total_results' => $query->count()] : []));
    }

    public function show(Request $request, Space $space, $reservation)
    {
        $this->authorize('can-do', ['reservation.show']);
        $reservation = $space->reservations()->where('id', $reservation)->firstOrFail();

        return (new ReservationResource($reservation))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function decodeQr(Request $request)
    {
        $this->authorize('can-do', ['reservation.show']);
        // Use regex to extract the form_id and data_id parts of the following string 'grey:multiverse:form=1:data=23'
        $this->validate($request, [
            'qr' => 'required|regex:/^grey:multiverse:reservation=(\d+):space=(\d+)$/',
        ], [
            'qr.regex' => 'The QR code is invalid.',
        ]);

        // Decode a regex string into an array
        preg_match('/^grey:multiverse:reservation=(\d+):space=(\d+)$/', $request->qr, $matches);
        $reservation_id = $matches[1];
        $space_id = $matches[2];

        $space = Space::whereId($space_id)->firstOrFail();
        $reservation = $space->reservations()->where('id', $reservation_id)->firstOrFail();

        // save this scan date
        $reservation->scan_date = now();
        $reservation->save();

        return (new ReservationResource($reservation))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }

    public function status(Request $request, Space $space, $reservation)
    {
        $this->authorize('can-do', ['reservation.update']);
        $reservation = $space->allReservations()->where('id', $reservation)->firstOrFail();

        $this->authorize('can-do', ['spaces.update']);
        $request->validate([
            'status' => 'required|string|in:pending,paid,cancelled,failed',
        ]);

        $user = $reservation->user_type === 'guest' ? $reservation->guest : $reservation->user;
        $transaction = $reservation->transactions()->whereUserId($user->id)->latest()->first();

        if ($transaction) {
            $transaction->update([
                'status' => $request->status,
            ]);
        }

        return (new ReservationResource($reservation))->additional([
            'message' => __('The reservation has been marked as :0!', [$request->status]),
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id = null)
    {
        $this->authorize('can-do', ['reservation.delete']);
        if ($request->items) {
            $count = collect($request->items)->map(function ($item) {
                $item = Reservation::find($item);
                if ($item) {
                    return $item->delete();
                }

                return false;
            })->filter(fn ($i) => $i !== false)->count();

            return $this->buildResponse([
                'message' => "{$count} reservations have been deleted.",
                'status' => 'success',
                'status_code' => HttpStatus::OK,
            ]);
        } else {
            $item = Reservation::findOrFail($id);
            if ($item) {
                $item->delete();

                return $this->buildResponse([
                    'message' => 'This reservation has been deleted.',
                    'status' => 'success',
                    'status_code' => HttpStatus::ACCEPTED,
                ]);
            }
        }
    }
}
