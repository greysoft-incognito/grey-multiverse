<?php

namespace App\Http\Controllers\Auth;

use App\Enums\HttpStatus;
use App\Helpers\Providers as PV;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use DeviceDetector\DeviceDetector;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required_without:firstname', 'string', 'max:255'],
            'email' => ['required_without:phone', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required_without:email', 'string', 'max:255', 'unique:users,phone', 'phone:INTERNATIONAL,NG'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required_without' => 'Please enter your fullname.',
        ], [
            'email' => 'Email Address',
            'phone' => 'Phone Number',
        ]);

        $user = $this->createUser($request);

        return $this->setUserData($request, $user);
    }

    /**
     * Create a new user based on the provided data.
     *
     * @return \App\Models\User
     */
    public function createUser(Request $request)
    {
        $firstname = str($request->get('name'))->explode(' ')->first(null, $request->firstname);
        $lastname = str($request->get('name'))->explode(' ')->last(fn($n) => $n !== $firstname, $request->lastname);

        $user = User::create([
            'role' => 'user',
            'type' => $request->get('type', 'farmer'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'gender' => $request->get('gender'),
            'password' => $request->get('password'),
            'lastname' => $request->get('lastname', $lastname ?? ''),
            'firstname' => $request->get('firstname', $firstname),
        ]);

        if (dbconfig('verify_email', false)) {
            $user->sendEmailVerificationNotification();
        }

        if (dbconfig('verify_phone', false)) {
            $user->sendPhoneVerificationNotification();
        }

        return $user;
    }

    public function setUserData(Request $request, User $user)
    {
        event(new Registered($user));

        $dev = new DeviceDetector($request->userAgent());

        $device = $dev->getBrandName()
            ? ($dev->getBrandName() . $dev->getDeviceName())
            : $request->userAgent();

        $user->save();

        $token = $user->createToken($device, ['user:access'])->plainTextToken;

        return $this->preflight($token);
    }

    /**
     * Log the newly registered user in.
     *
     * @param  string  $token
     * @return \App\Http\Resources\UserResource
     */
    public function preflight($token)
    {
        [$id, $user_token] = explode('|', $token, 2);

        $token_data = DB::table('personal_access_tokens')->where('token', hash('sha256', $user_token))->first();

        $user_id = $token_data->tokenable_id;

        Auth::loginUsingId($user_id);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var \Carbon\Carbon */
        $datetime = $user->last_attempt ?? now()->subSeconds(PV::config('token_lifespan', 30) + 1);
        $dateAdd = $datetime->addSeconds(PV::config('token_lifespan', 30));
        // dd($dateAdd->format('H:i:s'));

        return (new UserResource($user))->additional([
            'message' => 'Registration was successfull',
            'token' => $token,
            'time_left' => $dateAdd->shortAbsoluteDiffForHumans(),
            'try_at' => $dateAdd->toDateTimeLocalString(),
            'statusCode' => HttpStatus::CREATED,
        ])->response()->setStatusCode(HttpStatus::CREATED->value);
    }

    /**
     * Check the specified field.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function checks(Request $request)
    {
        $rule = $request->rule ?? 'unique';
        $type = $request->type ?? 'email';

        $validator = static fn($validator) => abort_if($validator->fails(), PV::response()->error([
            'data' => [],
            'errors' => $validator->messages(),
            'message' => $validator->messages()->all()[0] ?? 'error',
            'silent' => true,
        ], HttpStatus::UNPROCESSABLE_ENTITY));

        if ($type === 'email') {
            $validator(Validator::make($request->all(), [
                'email' => 'required|email',
            ], ['email' => 'Invalid email address']));
        }

        $validator(Validator::make($request->all(), [
            'type' => ['bail', 'required', 'string', 'in:email,phone'],
            'rule' => ['bail', 'required', 'string', 'in:unique'],
            $type => ['required', 'string', 'max:255', "{$rule}:users,{$type}"],
        ], [
            "{$type}.{$rule}" => "An account with this {$type} already exists.",
        ], [
            'email' => 'Email Address',
            'phone' => 'Phone Number',
        ]));

        return PV::response()->success([
            'data' => [],
            'message' => "The {$type} is available for use.",
        ], HttpStatus::ACCEPTED);
    }
}
