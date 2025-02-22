<?php

namespace V1\Services;

use Illuminate\Support\Facades\File;

class AppInfo
{
    /**
     * Contruct the the api info
     */
    public static function basic(int $version = null): array
    {
        return [
            'name' => config('app.name'),
            'version' => env('APP_VERSION', config("api.version.{$version}", '1.0.0')),
            'author' => 'Greysoft',
            'updated' => File::exists(base_path('.updated'))
                ? new \Carbon\Carbon(File::lastModified(base_path('.updated')))
                : now(),
        ];
    }

    /**
     * Put the api info into the api collection
     */
    public static function api(): array
    {
        return [
            'api' => self::basic(),
        ];
    }

    /**
     * Append extra data to the api info
     */
    public static function with($data = []): array
    {
        return array_merge(self::api(), $data);
    }
}
