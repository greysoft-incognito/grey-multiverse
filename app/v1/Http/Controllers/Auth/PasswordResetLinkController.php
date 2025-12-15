<?php

namespace V1\Http\Controllers\Auth;

use App\Models\PasswordCodeResets;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use V1\Http\Controllers\Controller;
use V1\Notifications\SendCode;
use V1\Services\HttpStatus;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users'],
        ], [
            'email.exists' => 'We could not find a user with this email address.',
        ]);

        if ($validator->fails()) {
            return $this->validatorFails($validator);
        }

        // Delete the old code
        PasswordCodeResets::whereEmail($request->email)->delete();

        // generate the new code
        $reset = new PasswordCodeResets;
        $reset->email = $request->email;
        $reset->code = mt_rand(100000, 999999);
        $reset->save();

        // Notify the user
        User::whereEmail($reset->email)->first()->notify(new SendCode($reset->code));

        // And finally return a response
        return $this->buildResponse([
            'message' => __('We have sent you a message to help with recovering your password.'),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            return $this->buildResponse([
                'message' => 'An error occured.',
                'status' => 'error',
                'status_code' => HttpStatus::UNPROCESSABLE_ENTITY,
                'errors' => [
                    'email' => __($status),
                ],
            ]);
        }

        return $this->buildResponse([
            'message' => __($status),
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }
}
