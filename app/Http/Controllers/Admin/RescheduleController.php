<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentRescheduleCollection;
use App\Http\Resources\AppointmentRescheduleResource;
use App\Models\BizMatch\Reschedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RescheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');
        $query = Reschedule::query();
        $query->forUser($user->id, $request->has('sent') ? $request->boolean('sent') : null);

        $data = $query->paginate($request->input('limit', 30));

        return (new AppointmentRescheduleCollection($data))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reschedule $reschedule)
    {
        return (new AppointmentRescheduleResource($reschedule))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Confirm or Cancel an appointment Reschedule request.
     */
    public function update(Request $request, Reschedule $reschedule)
    {
        $valid = $this->validate($request, [
            'status' => 'required|string|in:accepted,declined',
        ]);

        /**
         * Update the appointment
         */
        $appointment = $reschedule->appointment;
        $appointment->status = $valid['status'] === 'accepted' ? 'confirmed' : 'canceled';
        $appointment->time_slot = $reschedule->proposed_time_slot;
        $appointment->duration = $reschedule->proposed_duration;
        $appointment->date = $reschedule->proposed_date;

        if ($valid['status'] === 'accepted') {
            try {
                $appointment = $appointment->findNextAvailableSlot();
            } catch (ModelNotFoundException $th) {
                abort(Providers::response()->error([
                    'data' => [],
                    'errors' => ['time_slot' => [$th->getMessage()]],
                    'message' => $th->getMessage(),
                ], HttpStatus::UNPROCESSABLE_ENTITY));
            }
        }

        $appointment->saveQuietly();

        $reschedule->status = $valid['status'];
        $reschedule->save();

        return (new AppointmentRescheduleResource($appointment))->additional([
            'status' => 'success',
            'message' => __(Reschedule::$msgGroups['recipient'][$valid['status']], [$appointment->invitee->company->name]),
            'statusCode' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $ids = $request->input('items', [$id]);
        Reschedule::whereIn('id', $ids)->delete();

        return (new AppointmentRescheduleCollection([]))->additional([
            'message' => (count($ids) > 1 ? count($ids).' reschedules' : 'Reschedule').' deleted successfully',
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }
}
