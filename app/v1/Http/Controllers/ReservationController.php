<?php

namespace V1\Http\Controllers;

use App\Models\Guest;
use App\Models\Space;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use V1\Http\Resources\ReservationCollection;
use V1\Http\Resources\ReservationResource;
use V1\Services\HttpStatus;
use V1\Traits\Meta;

class ReservationController extends Controller
{
    use Meta;

    public function guest(Request $request, Space $space)
    {
        $phone_val = stripos($request->phone, '+') !== false || ! config('settings.verify_phone', false)
            ? 'phone:AUTO,NG'
            : 'phone:'.$this->ipInfo('country');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'], // 'unique:users'],
            'phone' => config('settings.verify_phone', false) ? "required|$phone_val" : 'nullable|string|max:255|unique:users',
            'company' => 'required|string|max:120',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
        ]);

        $name = str($request->name ?? $request->username)->explode(' ');

        $user = Guest::updateOrCreate(
            ['email' => $request->email],
            [
                'firstname' => $name->get(0, $request->name),
                'lastname' => $name->get(1),
                'phone' => $request->phone,
                'company' => $request->company,
            ]
        );

        event(new Registered($user));

        return $this->reserveNow($request, $space, $user, 'guest');
    }

    public function store(Request $request, Space $space)
    {
        $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
        ]);

        if ($space->available_spots <= 0) {
            $error = __('This space is already booked up');

            return $this->buildResponse([
                'message' => $error,
                'status' => 'error',
                'status_code' => HttpStatus::UNPROCESSABLE_ENTITY,
            ]);
        }

        $user = Auth::user();

        return $this->reserveNow($request, $space, $user, 'user');
    }

    protected function reserveNow(Request $request, Space $space, $user, $user_type)
    {
        if ($request->has('space_ids') && is_array($request->space_ids)) {
            $spaces = Space::whereIn('id', $request->space_ids)->get();
            $is_available = $spaces->mapWithKeys(function ($space, $key) {
                return [$space->id => ! $space->is_available ? __(':name is no longer available', ['name' => $space->name]) : true];
            });

            if (($errors = $is_available->filter(fn ($value) => $value !== true))->count() > 0) {
                $error = __('Some spaces you selected are no longer available');

                return $this->buildResponse([
                    'message' => $error,
                    'status' => 'error',
                    'status_code' => HttpStatus::UNPROCESSABLE_ENTITY,
                ], ['errors' => $errors]);
            }

            $reservations = $spaces->map(function ($space) use ($user, $request, $user_type) {
                $reservation = $space->reservations()->create([
                    'name' => $request->name,
                    'user_id' => $user->id,
                    'user_type' => $user_type,
                    'start_date' => $request->start_date ?? null,
                    'end_date' => $request->end_date ?? null,
                ]);

                $reference = config('settings.trx_prefix', 'TRX-').$this->generate_string(20, 3);

                return $reservation->transactions()->create([
                    'user_id' => $user->id,
                    'amount' => $space->price,
                    'due' => $space->price,
                    'reference' => $reference,
                    'method' => 'Manual',
                    'status' => 'pending',
                ]);
            });

            $counted = $reservations->count().' '.__('spaces');

            return (new ReservationCollection($reservations))->additional([
                'message' => __(':0 has been booked successfully, we would reach out to you with more information soon.', [$counted]),
                'status' => 'success',
                'status_code' => HttpStatus::CREATED,
            ])->response()->setStatusCode(HttpStatus::CREATED);
        } else {
            if ($space->available_spots <= 0) {
                $error = __('This space is already booked up');

                return $this->buildResponse([
                    'message' => $error,
                    'status' => 'error',
                    'status_code' => HttpStatus::BAD_REQUEST,
                ]);
            }

            $reservation = $space->reservations()->create([
                'name' => $request->name,
                'user_id' => $user->id,
                'user_type' => 'user',
                'start_date' => $request->start_date ?? null,
                'end_date' => $request->end_date ?? null,
            ]);

            $reference = config('settings.trx_prefix', 'TRX-').$this->generate_string(20, 3);

            $reservation->transactions()->create([
                'user_id' => $user->id,
                'amount' => $space->price,
                'due' => $space->price,
                'reference' => $reference,
                'method' => 'Manual',
                'status' => 'pending',
            ]);

            return (new ReservationResource($reservation))->additional([
                'message' => __(':0 has been booked successfully, we would reach out to you with more information soon.', [$space->name]),
                'status' => 'success',
                'status_code' => HttpStatus::CREATED,
            ])->response()->setStatusCode(HttpStatus::CREATED);
        }
    }
}
