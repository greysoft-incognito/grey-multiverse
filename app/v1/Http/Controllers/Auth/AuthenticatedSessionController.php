<?php

namespace V1\Http\Controllers\Auth;

use DeviceDetector\DeviceDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use V1\Http\Controllers\Controller;
use V1\Http\Requests\Auth\LoginRequest;
use V1\Http\Resources\User\UserResource;
use V1\Services\HttpStatus;
use V1\Traits\Renderer;

class AuthenticatedSessionController extends Controller
{
    use Renderer;

    public function index()
    {
        if ($user = Auth::user()) {
            $errors = $code = $messages = $action = null;

            return view('web-user', compact('user', 'errors', 'code', 'action'));
        }

        return view('login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();

            $dev = new DeviceDetector($request->userAgent());
            $device = $dev->getBrandName() ? ($dev->getBrandName().$dev->getDeviceName()) : $request->userAgent();

            $user = $request->user();

            $token = $user->createToken($device)->plainTextToken;
            $this->setUserData($user);

            return (new UserResource($user))->additional([
                'message' => 'Login was successfull',
                'status' => 'success',
                'status_code' => HttpStatus::OK,
                'token' => $token,
            ])->response()->setStatusCode(HttpStatus::OK);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->buildResponse([
                'message' => $e->getMessage(),
                'status' => 'error',
                'status_code' => HttpStatus::UNPROCESSABLE_ENTITY,
                'errors' => [
                    'email' => $e->getMessage(),
                ],
            ]);
        }
    }

    public function setUserData($user)
    {
        $user->access_data = $this->ipInfo();
        $user->save();
    }

    /**
     * Destroy an authenticated session.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $request->user()->tokens()->delete();

        if (! $request->isXmlHttpRequest()) {
            session()->flush();

            return response()->redirectToRoute('web.login');
        }

        return $this->buildResponse([
            'message' => 'You have been successfully logged out',
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }
}
