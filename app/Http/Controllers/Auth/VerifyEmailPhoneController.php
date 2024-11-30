<?php

namespace App\Http\Controllers\Auth;

use App\Enums\HttpStatus;
use App\Events\Verified;
use App\Helpers\Providers as PV;
use App\Helpers\Providers;
use App\Helpers\Url;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VerifyEmailPhoneController extends Controller
{
    /**
     * Send a new email verification notification.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $type = 'email')
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $set_type = ($type == 'phone') ? 'phone number' : 'email address';
        $hasVerified = ($type == 'phone') ? $user->hasVerifiedPhone() : $user->hasVerifiedEmail();

        if ($hasVerified) {
            return PV::response()->info(new UserResource($user), HttpStatus::OK, [
                'reboot' => true,
                'message' => "Your $set_type is already verified.",
            ]);
        }

        if ($type === 'email') {
            $user->sendEmailVerificationNotification();
        }

        if ($type === 'phone') {
            $user->sendPhoneVerificationNotification();
        }

        /** @var \Carbon\Carbon */
        $datetime = $user->last_attempt ?? now()->subSeconds(PV::config('token_lifespan', 30) + 1);
        $dateAdd = $datetime->addSeconds(PV::config('token_lifespan', 30));

        return PV::response()->success(new UserResource($user), HttpStatus::CREATED, [
            'message' => "Verification code has been sent to your {$set_type}.",
            'time_left' => $dateAdd->shortAbsoluteDiffForHumans(),
            'try_at' => $dateAdd->toDateTimeLocalString(),
        ]);
    }

    /**
     * Ping the verification notification to know the status.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, $type = 'email')
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $set_type = ($type == 'phone') ? 'phone number' : 'email address';

        /** @var \Carbon\Carbon */
        $datetime = $user->last_attempt ?? now()->subSeconds(PV::config('token_lifespan', 30) + 1);

        $dateAdd = $datetime->addSeconds(PV::config('token_lifespan', 30));
        $hasVerified = ($type == 'phone') ? $user->hasVerifiedPhone() : $user->hasVerifiedEmail();

        return PV::response()->success(new UserResource($user), HttpStatus::OK, [
            'reboot' => (bool) $hasVerified,
            'message' => $hasVerified
                ? "We have successfully verified your $set_type, welcome to our community."
                : "Your $set_type is not yet verified.",
            'time_left' => $dateAdd->shortAbsoluteDiffForHumans(),
            'try_at' => $dateAdd->toDateTimeLocalString(),
            'verified' => $hasVerified,
        ]);
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $type = 'email')
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $set_type = ($type == 'phone') ? 'phone number' : 'email address';

        $hasVerified = ($type == 'phone')
            ? $user->hasVerifiedPhone()
            : $user->hasVerifiedEmail();

        if ($hasVerified) {
            return PV::response()->info(new UserResource($user), HttpStatus::OK, [
                'reboot' => true,
                'message' => "Your $set_type is already verified.",
            ]);
        }

        Validator::make($request->all(), [
            'code' => ['required'],
        ])->validate();

        $code = ($type == 'email')
            ? $user->email_verify_code
            : (
                $type == 'phone'
                ? $user->phone_verify_code
                : null
            );

        $requestCode = Url::base64urlDecode($request->code);

        if (str($requestCode)->contains('|')) {
            $requestCode = str($requestCode)->explode('|')->first();
            $error = 'The link you followed has expired, please request a new verification link.';
        } else {
            $requestCode = $request->code;
            $error = 'The code you provided has expired or does not exist.';
        }

        // check if it has not expired: the time is 30 minutes and that the code is valid
        $last_attempt = ($user->hasVerifiedPhone() && $user->last_attempt === null)
            ? $user->phone_verified_at
            : $user->last_attempt;

        if ($requestCode !== $code || $last_attempt->diffInMinutes(now()) >= PV::config('token_lifespan', 30)) {
            return PV::response()->error([
                'errors' => ['code' => __($error)],
            ], HttpStatus::UNPROCESSABLE_ENTITY);
        }

        if ($type == 'email' && $user->markEmailAsVerified()) {
            event(new Verified($user, $type));
        }

        if ($type == 'phone' && $request->user()->markPhoneAsVerified()) {
            event(new Verified($user, $type));
        }

        return PV::response()->success(new UserResource($user), HttpStatus::OK, [
            'reboot' => true,
            'message' => "We have successfully verified your $set_type, welcome to our community.",
        ]);
    }

    /**
     * Validate User OTP.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function otp(Request $request)
    {
        $type = $request->type ?? 'email';
        $table = $request->boolean('temp') ? 'temp_users' : 'users';

        $this->validate($request, [
            'otp' => ['nullable', 'string'],
            'type' => ['bail', 'required', 'string', 'in:email,phone'],
            'temp' => ['nullable', 'boolean'],
            $type => ['required', 'string', 'max:255', $request->otp ? "exists:$table,$type" : 'bail'],
        ], [
            'email' => 'You have entered an invalid email address'
        ], [
            'otp' => 'OTP',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
        ]);

        /** @var \Illuminate\Database\Eloquent\Builder<\App\Models\User|\App\Models\TempUser> */
        $model = str($table)->singular()->camel()->title()->prepend('\\App\\Models\\')->toString()::query();

        if (!$request->otp) {
            $user = $request->temp
                ? $model->firstOrCreate([$type => $request->{$type}])
                : $model->where($type, $request->{$type})->firstOrFail();
            $user->sendOTPNotification();

            $masked = str($request[$type])->mask('*', 3, mb_strlen($request[$type]) / 2);

            return PV::response()->success([
                'data' => [],
                'message' => "An OTP has been sent to {$masked}.",
            ], HttpStatus::CREATED);
        } else {
            $ls = Providers::config('token_lifespan', 30);
            $user = $model->firstWhere([$type => $request->{$type}, 'otp' => $request->otp]);

            abort_if(!$user || now()->diffInSeconds($user->last_attempt) >= $ls, PV::response()->error([
                'data' => [],
                'errors' => ['otp' => ["This OTP is no longer valid."]],
                'message' => "This OTP is no longer valid.",
                'silent' => true,
            ], HttpStatus::TIMEOUT));

            if ($user instanceof \App\Models\TempUser) {
                $user->delete();
            } else {
                $user->update(['last_attempt' => now(), 'otp' => null]);
            }

            return PV::response()->success([
                'data' => [],
                'message' => "The OTP is valid.",
            ], HttpStatus::ACCEPTED);
        }
    }
}