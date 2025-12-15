<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentCollection;
use App\Http\Resources\AppointmentResource;
use App\Models\BizMatch\Appointment;
use App\Models\BizMatch\Reschedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Appointment::query();

        $data = $query->paginate($request->input('limit', 30));

        return (new AppointmentCollection($data))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        return (new AppointmentResource($appointment))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Confirm, Reschedule or Cancel an appointment.
     */
    public function update(Request $request, Appointment $appointment)
    {
        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');

        $valid = $this->validate($request, [
            'status' => 'required|string|in:confirmed,rescheduled,canceled',
            'date' => 'required_if:status,rescheduled|date',
            'duration' => 'required_if:status,rescheduled|numeric|in:15,20,25,30',
            'time_slot' => 'required_if:status,rescheduled|string|in:morning,afternoon,evening',
            'message' => ['nullable', 'string', 'min:1', 'max:1000'],
        ]);

        /**
         * Set the new appointment status
         */
        $appointment->status = $valid['status'];

        if ($valid['status'] === 'confirmed') {
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

        /**
         * Create a reschedule model
         */
        if ($appointment->status === 'rescheduled') {
            $appointment->reschedules()->updateOrCreate([
                'appointment_id' => $appointment->id,
            ], [
                'proposed_date' => $valid['date'],
                'proposed_duration' => $valid['duration'],
                'proposed_time_slot' => $valid['time_slot'],
            ]);

            // Update the appointment
            $appointment->saveQuietly();

            $msg = __(Reschedule::$msgGroups['sender']['pending'], [0, $appointment->requestor->company->name]);
        } else {
            // Update the appointment
            $appointment->save();
            // Delete all reschedule requests
            $appointment->reschedules()->delete();

            $msg = __(Appointment::$msgGroups['recipient'][$valid['status']], [0, $appointment->invitee->company->name]);
        }

        if ($valid['message']) {
            $appointment->sendMessage($user, $valid['message']);
        }

        return (new AppointmentResource($appointment))->additional([
            'status' => 'success',
            'message' => $msg,
            'statusCode' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $ids = $request->input('items', [$id]);
        Appointment::whereIn('id', $ids)->delete();

        return (new AppointmentCollection([]))->additional([
            'message' => (count($ids) > 1 ? count($ids).' appointments' : 'Appointment').' deleted successfully',
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }
}
