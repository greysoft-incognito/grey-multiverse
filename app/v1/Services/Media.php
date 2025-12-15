<?php

namespace V1\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use V1\Traits\Meta;

class Media
{
    use Meta;

    public $globalDefault = false;

    public $default_media = 'media/default.png';

    public $namespaces = [
        'avatar' => [
            'size' => [400, 400],
            'path' => 'avatars/',
            'default' => 'default.png',
        ],
        'default' => [
            'path' => 'media/images/',
            'default' => 'default.png',
        ],
        'logo' => [
            'size' => [200, 200],
            'path' => 'media/logos/',
            'default' => 'default.png',
        ],
        'private' => [
            'images' => [
                'path' => 'files/images/',
                'default' => 'default.png',
            ],
        ],
        'banner' => [
            'size' => [1200, 600],
            'path' => 'media/banners/',
            'default' => 'default.png',
        ],
    ];

    public function __construct()
    {
        $this->globalDefault = true;
        $this->imageDriver = new ImageManager(['driver' => 'gd']);
    }

    /**
     * Fetch an image from the storage
     */
    public function image(string $type, string $src = null, $get_path = false): ?string
    {
        $getPath = Arr::get($this->namespaces, $type.'.path');
        $default = Arr::get($this->namespaces, $type.'.default');
        $prefix = ! str($type)->contains('private.') ? 'public/' : '/';

        if (filter_var($src, FILTER_VALIDATE_URL)) {
            return str($src)->replace('localhost', request()->getHttpHost());
        }

        if (! $src || ! Storage::exists($prefix.$getPath.$src)) {
            if (filter_var($default, FILTER_VALIDATE_URL)) {
                return $default;
            } elseif (! Storage::exists($prefix.$getPath.$default)) {
                if ($get_path === true) {
                    return Storage::path($this->default_media);
                }

                return asset($this->default_media);
            }

            if ($get_path === true) {
                return Storage::path($getPath.$default);
            }

            return asset($getPath.$default);
        }

        if (str($type)->contains('private.')) {
            return route('get.image', ['file' => base64url_encode($getPath.$src)]);
        }

        if ($get_path === true) {
            return Storage::path($getPath.$src);
        }

        return asset($getPath.$src);
    }

    public function privateFile($file)
    {
        $src = base64url_decode($file);
        if (Storage::exists($src)) {
            $mime = Storage::mimeType($src);
            // create response and add encoded image data
            if (! str($mime)->contains('image')) {
                $img = $this->imageDriver->make(storage_path('app/'.$src));
                $response = Response::make($img->encode(str($mime)->explode('/')->last()));
            } else {
                $response = Response::make(Storage::get($src));
            }

            // set headers
            return $response->header('Content-Type', $mime)
                ->header('Cross-Origin-Resource-Policy', 'cross-origin')
                ->header('Access-Control-Allow-Origin', '*');
        }
    }

    /**
     * Fetch an image from the storage
     *
     * @param  string  $old
     */
    public function save(string $type, string $file_name = null, $old = null): ?string
    {
        $getPath = Arr::get($this->namespaces, $type.'.path');
        $prefix = ! str($type)->contains('private.') ? 'public/' : '/';

        $request = request();
        if ($request->hasFile($file_name)) {
            $old_path = $prefix.$getPath.$old;

            if ($old && Storage::exists($old_path) && $old !== 'default.png') {
                Storage::delete($old_path);
            }
            $rename = rand().'_'.rand().'.'.$request->file($file_name)->extension();

            $request->file($file_name)->storeAs(
                $prefix.trim($getPath, '/'), $rename
            );
            $request->offsetUnset($file_name);

            // Resize the image
            $size = Arr::get($this->namespaces, $type.'.size');
            if ($size) {
                $this->imageDriver->make(storage_path('app/'.$prefix.$getPath.$rename))
                    ->fit($size[0], $size[1])
                    ->save();
            }

            return $rename;
        }

        return $old;
    }

    /**
     * Delete an image from the storage
     */
    public function delete(string $type, string $src = null): ?string
    {
        $getPath = Arr::get($this->namespaces, $type.'.path');
        $prefix = ! str($type)->contains('private.') ? 'public/' : '/';

        $path = $prefix.$getPath.$src;

        if ($src && Storage::exists($path) && $src !== 'default.png') {
            Storage::delete($path);
        }

        return $path;
    }
}
