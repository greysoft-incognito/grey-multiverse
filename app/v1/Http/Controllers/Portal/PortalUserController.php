<?php

namespace V1\Http\Controllers\Portal;

use App\Models\GenericFormData;
use App\Models\Guest;
use App\Models\Portal\Portal;
use DeviceDetector\DeviceDetector;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use V1\Http\Controllers\Auth\RegisteredUserController;
use V1\Http\Controllers\Controller;
use V1\Http\Controllers\Guest\FormDataController;
use V1\Http\Requests\Auth\LoginRequest;
use V1\Http\Resources\FormDataResource;
use V1\Http\Resources\Portal\PortalResource;
use V1\Http\Resources\User\UserResource;
use V1\Notifications\FormSubmitedSuccessfully;
use V1\Services\HttpStatus;

class PortalUserController extends Controller
{
    public function register(Request $request, Portal $portal)
    {
        $request->validate([
            'email' => 'required|email|unique:'.$portal->registration_model.',email',
            'password' => 'required|string|confirmed',
        ]);

        $data = (new FormDataController)->store($request, $portal->reg_form_id, true);

        $name = str($data['name'] ?? $data['fullname'] ?? $data['full_name'] ?? $request->email)->explode(' ');

        $userModel = app($portal->registration_model ?? Guest::class);

        $user = $userModel->updateOrCreate(
            ['email' => $request->email],
            [
                'firstname' => $name->get(0, $data['firstname'] ?? $data['first_name'] ?? ''),
                'lastname' => $name->get(1, $data['lastname'] ?? $data['last_name'] ?? ''),
                'phone' => $data['phone'] ?? $data['mobile'] ?? $data['mobile_number'] ?? $data['phone_number'] ?? '',
                'password' => $request->password,
            ]
        );

        event(new Registered($user));

        $key = $portal->regForm->fields->firstWhere('key', true)->name ?? $portal->regForm->fields->first()->name;
        $formdata = GenericFormData::create([
            'form_id' => $portal->reg_form_id,
            'user_id' => $user->id ?? null,
            'data' => $data,
            'key' => $data[$key] ?? '',
        ]);

        $formdata->notify(new FormSubmitedSuccessfully());

        $dev = new DeviceDetector($request->userAgent());
        $device = $dev->getBrandName() ? ($dev->getBrandName().$dev->getDeviceName()) : $request->userAgent();

        $token = $user->createToken($device)->plainTextToken;
        (new RegisteredUserController)->setUserData($user);

        return (new RegisteredUserController)->preflight($token, [
            'portal' => new PortalResource($portal),
            'formdata' => new FormDataResource($formdata),
            'message' => __('Your registration for :portal has was completed successfully.', ['portal' => $portal->name]),
            'status' => 'success',
            'status_code' => HttpStatus::CREATED,
        ], $portal->registration_model === Guest::class ? 'guest' : null, $user);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request, Portal $portal)
    {
        try {
            $request->authenticateGuest($portal);

            $dev = new DeviceDetector($request->userAgent());
            $device = $dev->getBrandName() ? ($dev->getBrandName().$dev->getDeviceName()) : $request->userAgent();

            $user = $request->user();

            $token = $user->createToken($device)->plainTextToken;
            (new RegisteredUserController)->setUserData($user);

            return (new UserResource($user))->additional([
                'message' => 'Login was successfull',
                'status' => 'success',
                'status_code' => HttpStatus::OK,
                'token' => $token,
            ])->response()->setStatusCode(HttpStatus::OK);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->buildResponse([
                'portal' => new PortalResource($portal),
                'message' => $e->getMessage(),
                'status' => 'error',
                'status_code' => HttpStatus::UNPROCESSABLE_ENTITY,
                'errors' => [
                    'email' => $e->getMessage(),
                ],
            ]);
        }
    }

    public function show(Request $request, Portal $portal)
    {
        $user = $request->user();
        $user_data = $portal->regForm->data()->where('user_id', $user->id ?? '--')->first();

        return (new UserResource($user))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
            'user_data' => $user_data,
            'portal' => $portal,
        ])->response()->setStatusCode(HttpStatus::OK);
    }
}
