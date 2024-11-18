<?php

namespace V1\Http\Controllers\Auth;

use App\Models\User;
use DeviceDetector\DeviceDetector;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use V1\Http\Controllers\Controller;
use V1\Http\Resources\User\UserResource;
use V1\Services\HttpStatus;
use V1\Traits\Renderer;

class RegisteredUserController extends Controller
{
    use Renderer;

    /**
     * Handle an incoming registration request.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // $phone_val = stripos($request->phone, '+') !== false ? 'phone:AUTO,NG' : 'phone:'.$this->ipInfo('country');

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            // 'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // 'phone' => config('settings.verify_phone', false) ? "required|$phone_val" : 'nullable|string|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [], [
        ]);

        if ($validator->fails()) {
            return $this->validatorFails($validator);
        }

        $name = Str::of($request->name ?? $request->username)->explode(' ');

        $user = User::create([
            'firstname' => ($firstname = $name->first(fn ($k) => $k !== null, $request->name)),
            'lastname' => $name->last(fn ($k) => $k !== $firstname, ''),
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        $dev = new DeviceDetector($request->userAgent());
        $device = $dev->getBrandName() ? ($dev->getBrandName().$dev->getDeviceName()) : $request->userAgent();

        $token = $user->createToken($device)->plainTextToken;
        $this->setUserData($user);

        return $this->preflight($token);
    }

    public function setUserData($user)
    {
        $user->access_data = $this->ipInfo();
        $user->save();
    }

    public function preflight($token, $responseData = [], $gaurd = null, $user = null)
    {
        [$id, $user_token] = explode('|', $token, 2);
        $token_data = DB::table('personal_access_tokens')->where('token', hash('sha256', $user_token))->first();
        $user_id = $token_data->tokenable_id;

        if ($user) {
            Auth::login($user);
        } else {
            Auth::loginUsingId($user_id);
            $user = Auth::guard($gaurd)->user();
            $user->subscription;
        }

        return (new UserResource($user))->additional(array_merge([
            'message' => 'Registration was successfull',
            'status' => 'success',
            'status_code' => HttpStatus::CREATED,
            'token' => $token,
        ], $responseData))->response()->setStatusCode(HttpStatus::CREATED);
    }
}
