<?php

namespace V1\Http\Controllers;

use App\Helpers\Providers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use V1\Services\HttpStatus;

class HomeController extends Controller
{
    protected $file_types = [
        'image' => '.jpg, .png, .jpeg',
        'video' => '.mp4',
        'all' => 'audio/*, video/*, image/*',
    ];

    /**
     * Display the settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function settings(Request $request)
    {
        return $this->buildResponse([
            'message' => 'OK',
            'status' => 'success',
            'status_code' => HttpStatus::OK,
            'settings' => collect(Providers::config())
                ->except(['permissions', 'messages', 'system'])
                ->filter(fn ($v, $k) => stripos($k, 'secret') === false)
                ->mergeRecursive([
                ]),
            'csrf_token' => csrf_token(),
        ]);
    }

    public function manageFormFields(Request $request, $action, $task = null)
    {
        $disk = Storage::disk('protected');
        if (! $disk->exists('company_verification_data.json')) {
            $disk->put('company_verification_data.json', '[]');
        }

        $data = collect(json_decode($disk->get('company_verification_data.json'), JSON_FORCE_OBJECT))->map(function ($data) {
            if ($data['type'] === 'file') {
                $data['preview'] = $data['name'];
            } elseif ($data['type'] === 'checkbox') {
                $data['boolean'] = true;
                $data['highlight'] = true;
                $data['traditional'] = true;
            }

            return $data;
        });

        if ($action === 'save') {
            $this->validate($request, [
                'data' => ['required', 'array'],
                'data.*.label' => ['required', 'string'],
                'data.*.type' => ['required', 'string', 'in:text,checkbox,number,file'],
                'data.*.col' => ['required', 'numeric', 'min:1', 'max:12'],
                'data.*.file_type' => ['required_if:data.*.field_type,file', 'string', 'in:image,video,all'],
            ], [], [
                'data.*.label' => 'Field #:position Label',
                'data.*.type' => 'Field #:position Type',
                'data.*.col' => 'Field #:position Cols',
                'data.*.file_type' => 'File #:position Type',
            ]);

            $data = collect($request->data)->map(function ($data) {
                $data['name'] = str($data['label'])->slug('_')->toString();
                if ($data['type'] === 'file') {
                    $data['accept'] = $this->file_types[$data['file_type']];
                }

                return $data;
            })->toJson(JSON_PRETTY_PRINT);
            $disk->put('company_verification_data.json', $data);
        }

        return $this->buildResponse([
            'data' => $data,
            'message' => $action === 'save' ? 'Company Verification Data has been updated.' : 'OK',
            'status' => 'success',
            'status_code' => HttpStatus::OK,
        ]);
    }
}