<?php

namespace App\Http\Controllers\User;

use App\Enums\HttpStatus;
use App\Helpers\Providers;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ToneflixCode\LaravelFileable\Media;

class AccountController extends Controller
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'new_password_confirmation' => 'New Password Confirmation',
        'new_password' => 'New Password',
        'otp' => 'One Time Password',
        'password' => 'Password',
        'firstname' => 'Firstname',
        'lastname' => 'Lastname',
        'email' => 'Email Address',
        'country' => 'Country',
        'state' => 'State',
        'city' => 'City',
        'address' => 'Address',
        'image' => 'Profile Photo',
        'phone' => 'Phone Number',
        'gender' => 'Gender',
    ];

    /**
     * The user data attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillableData = [
        'notification' => 'array',
        'settings' => 'array',
        'bank' => 'array',
    ];

    public function ping()
    {
        return response()->json([
            'message' => 'PONG',
        ], HttpStatus::OK->value);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return (new UserResource(auth()->user()))->additional([
            'message' => HttpStatus::message(HttpStatus::OK),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ])->response()->setStatusCode(HttpStatus::OK->value);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $identifier = null)
    {
        /** @var \App\Models\User */
        $user = $request->user();

        $filled = collect($request->all());
        $fields = collect($request->all())->only(array_keys($this->fillable))->keys();

        $updated = [];

        $valid = $fields->mapWithKeys(function ($field) use ($filled, $user) {
            if (str($field)->contains(':image')) {
                $field = current(explode(':image', $field));
            }

            $vals = ! in_array($field, ['city', 'state']) ? ['required'] : ['nullable'];

            $vals[] = $field == 'image' ? 'mimes:png,jpg' : (
                is_array($filled[$field])
                ? 'array'
                : (
                    is_int($filled[$field])
                    ? 'numeric'
                    : 'string'
                )
            );

            if ($field === 'new_password') {
                $vals[] .= 'min:8';
                $vals[] .= 'confirmed';
            }

            if ($field === 'password') {
                $vals[] = 'current_password';
            }

            if ($field === 'phone') {
                $vals[] .= 'max:15';
                $vals[] .= Rule::unique('users')->ignore($user->id);
            }

            if ($field === 'email') {
                $vals[] .= 'email';
                $vals[] .= 'max:255';
                $vals[] .= Rule::unique('users')->ignore($user->id);
            }

            if (is_array($filled[$field])) {
                return [$field.'.*' => ['required']];
            }

            return [$field => $vals];
        })->all();

        if ($request->has('otp')) {
            $this->validate(
                $request,
                ['otp' => ['string', 'required']],
                ['otp' => 'A valid OTP is required.']
            );

            if (now()->diffInMinutes($user->last_attempt ?? now()->subHour()) > 5 || $user->otp != $request->otp) { // 5 minutes
                $user->otp = null;
                $user->save();
                throw ValidationException::withMessages([
                    'otp' => __('This OTP has expired.'),
                ]);
            }
        }

        $this->validate($request, $valid, [], $fields->filter(function ($k) use ($filled) {
            return is_array($filled[$k]);
        })->mapWithKeys(function ($field, $value) use ($filled) {
            return collect(array_keys((array) $filled[$field]))->mapWithKeys(fn ($k) => ["$field.$k" => "$field $k"]);
        })->all());

        $fields = $fields->filter(function ($k) {
            return ! str($k)->contains('_confirmation');
        });

        if (! $request->hasFile('image')) {
            foreach ($fields as $_field) {
                if (str($_field)->contains(':image')) {
                    $_field = current(explode(':image', (string) $_field));
                }

                if (! in_array($_field, [
                    'otp',
                    'password',
                    'new_password',
                    'new_password_confirmation',
                ])) {
                    $updated[$_field] = $request->{$_field};
                }

                if ($_field === 'email') {
                    $user->email = $request->email;
                    if (Providers::config('verify_email', false)) {
                        $user->email_verified_at = null;
                        $user->sendEmailVerificationNotification();
                    }
                } elseif ($_field === 'phone') {
                    $user->phone = $request->phone;
                    if (Providers::config('verify_phone', false)) {
                        $user->phone_verified_at = null;
                        $user->sendPhoneVerificationNotification();
                    }
                } elseif ($_field === 'new_password') {
                    $user->password = $request->new_password;
                    $user->data['password_updated'] = now();
                } else {
                    $user->{$_field} = $request->{$_field};
                }
            }
        }

        // Check if the user data needs to be updated
        foreach ([$request->user_data, $request->settings] as $index => $dataSet) {
            collect((array) $dataSet)->map(function ($val, $key) use ($user, $index) {
                $data = $user->{$index == 0 ? 'data' : 'settings'};
                // Check if the user data attribute is allowed to be modified
                if (in_array($key, array_keys($this->fillableData))) {
                    $value = match ($this->fillableData[$key]) {
                        'boolean' => filter_var($val, FILTER_VALIDATE_BOOL),
                        'array' => (array) $val,
                        default => $val
                    };
                    $data[$key] = $value;
                    $user->{$index == 0 ? 'data' : 'settings'} = $data;
                }
            });
        }

        $fields = collect($request->keys())->filter(
            fn ($k) => ! in_array($k, [
                'otp',
                '_method',
                'password',
            ])
        );

        $msg = $fields->count() > 1 && empty($this->fillable[$identifier])
            ? 'Your profile has been updated successfully.'
            : 'Your :0 has been successfully updated.';

        if ($request->has('otp')) {
            $user->otp = null;
            $user->last_attempt = null;
        }

        $user->save();

        return (new UserResource($user))->additional([
            'message' => __($msg, [
                str($this->fillable[$identifier] ?? $this->fillable[$fields->first()] ?? 'profile')->lower(),
            ]),
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
            'image' => $user->avatar,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Update the user profile picture.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProfilePicture(Request $request)
    {
        $user = auth()->user();

        $this->validate($request, [
            'image' => ['required', 'image', 'mimes:png,jpg', 'max:1024'],
        ], [
            'image.required' => 'You did not select an image for upload',
        ]);

        $user->image = (new Media())->save('avatar', 'image', $user->image);
        $user->updated_at = \Carbon\Carbon::now();
        $user->saveQuietly();

        return (new UserResource($user))->additional([
            'message' => 'Your profile picture has been changed successfully',
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }

    /**
     * Update the user password.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return Providers::response()->error([
                'message' => 'Your input has a few errors',
                'status' => 'error',
                'status_code' => HttpStatus::UNPROCESSABLE_ENTITY,
                'errors' => ['current_password' => ['Your current password is not correct.']],
            ]);
        }

        $this->validate($request, [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'confirmed'],
        ]);

        $user->password = $request->new_password;
        $user->data['password_updated'] = now();
        $user->save();

        return (new UserResource($user))->additional([
            'message' => 'Your password has been successfully updated',
            'status' => 'success',
            'status_code' => HttpStatus::ACCEPTED,
        ])->response()->setStatusCode(HttpStatus::ACCEPTED->value);
    }
}
