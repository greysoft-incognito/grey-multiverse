<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Resources\AppointmentCollection;
use App\Http\Resources\AppointmentResource;
use App\Models\BizMatch\Appointment;
use App\Models\BizMatch\Company;
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
        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');

        $query = Appointment::query();
        $query->forUser($user->id, $request->has('sent') ? $request->boolean('sent') : null);

        $data = $query->paginate($request->input('limit', 30));

        return (new AppointmentCollection($data))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Create a new appointment request.
     */
    public function store(Request $request)
    {
        @['message' => $message] = $this->validate($request, [
            'date' => 'required|date',
            'time_slot' => 'required|string|in:morning,afternoon,evening',
            'duration' => 'required|numeric|in:15,20,25,30',
            // 'table_number' => 'required|numeric|min:1|max:100',
            'company_id' => 'required|exists:companies,id',
            'message' => ['nullable', 'string', 'min:1', 'max:1000'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');

        abort_if(! $user->company, Providers::response()->error(['data' => [], 'message' => 'You have not registered your company.']));

        /** @var \App\Models\Company $company */
        $company = Company::find($request->company_id);

        /** @var \App\Models\BizMatch\Appointment $appointment */
        $appointment = $company->appointments()->make();

        $appointment->requestor_id = $user->id;
        $appointment->table_number = $request->integer('table_number');
        $appointment->invitee_id = $company->user->id;
        $appointment->time_slot = $request->time_slot;
        $appointment->duration = $request->duration;
        $appointment->status = 'pending';
        $appointment->date = $request->date;

        try {
            $appointment = $appointment->findNextAvailableSlot();
        } catch (ModelNotFoundException $th) {
            abort(Providers::response()->error([
                'data' => [],
                'errors' => ['time_slot' => [$th->getMessage()]],
                'message' => $th->getMessage(),
            ], HttpStatus::UNPROCESSABLE_ENTITY));
        }

        $appointment->save();

        if ($message) {
            $appointment->sendMessage($user, $message);
        }

        return (new AppointmentResource($appointment))->additional([
            'status' => 'success',
            'message' => __(Appointment::$msgGroups['sender']['pending'], [$company->name]),
            'statusCode' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $appointment_id)
    {
        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');

        /** @var \App\Models\BizMatch\Appointment $appointment */
        $appointment = Appointment::forUser($user->id)->findOrFail($appointment_id);

        return (new AppointmentResource($appointment))->additional([
            'status' => 'success',
            'message' => HttpStatus::message(HttpStatus::OK),
            'statusCode' => HttpStatus::OK,
        ]);
    }

    /**
     * Confirm, Reschedule or Cancel an appointment.
     */
    public function update(Request $request, string $appointment_id)
    {
        /** @var \App\Models\User $user */
        $user = $request->user('sanctum');

        /** @var \App\Models\BizMatch\Appointment $appointment */
        $appointment = Appointment::forUser($user->id)->findOrFail($appointment_id);

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
}
