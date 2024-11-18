<?php

namespace V1\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use V1\Services\AppInfo;
use V1\Services\HttpStatus;

/**
 * Provide methods that determine how response should be generated.
 */
trait Renderer
{
    /**
     * Prepare the API response
     *
     * @param  array  $data
     * @return void
     */
    public function buildResponse($data = [], $extra_data = null)
    {
        $message = $data['message'] ?? 'Request was successful';
        $code = $data['status_code'] ?? HttpStatus::OK;
        $resp = $data['response_data'] ?? null;
        $status = $data['status'] ?? 'success';
        $errors = $data['errors'] ?? null;
        $token = $data['token'] ?? null;

        unset($data['message'], $data['status_code'], $data['status'], $data['errors'], $data['token'], $data['response_data']);

        $response = [
            'api' => AppInfo::basic(),
            'message' => $message,
            'status' => $status,
            'status_code' => $code,
            // ...($data ?? ['data'=>[]]),
            'data' => $data['data'] ?? $data ?? [],
        ];

        if ($extra_data) {
            $response = array_merge($response, is_array($extra_data) ? $extra_data : ['load' => $extra_data]);
        }

        if ($errors) {
            $response['errors'] = $errors;
        }

        if ($token) {
            $response['token'] = $token;
        }

        if ($resp) {
            $response['response_data'] = $resp;
        }

        return response($response, $code);
    }

    /**
     * Prepare the validation error.
     *
     * @return void
     */
    public function validatorFails(Validator $validator, $field = null)
    {
        return $this->buildResponse([
            'message' => $field ? $validator->errors()->first() : 'Your input has a few errors',
            'status' => 'error',
            'status_code' => HttpStatus::UNPROCESSABLE_ENTITY,
            'errors' => $validator->errors(),
        ]);
    }

    public function time()
    {
        return time();
    }

    public function ip()
    {
        $ip = request()->ip();
        if (stripos($ip, '127.0.0') !== false && env('APP_ENV') === 'local') {
            $ip = '197.210.76.68';
        }

        return $ip;
    }

    public function ipInfo($key = null)
    {
        $info['country'] = 'US';
        if (($u = Auth::user()) && $u->access_data) {
            $info = $u->access_data;
        } else {
            return;
            $ipInfo = \Illuminate\Support\Facades\Http::get('ipinfo.io/'.$this->ip().'?token='.config('settings.ipinfo_access_token'));
            if ($ipInfo->status() === 200) {
                $info = $ipInfo->json() ?? $info;
            }
        }

        return $key ? ($info[$key] ?? '') : $info;
    }
}
