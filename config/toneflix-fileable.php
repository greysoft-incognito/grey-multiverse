<?php

//
return [
    'collections' => [
        'avatar' => [
            'size' => [400, 400],
            'path' => 'avatars/',
            'default' => 'default.svg',
        ],
        'banner' => [
            'size' => [1200, 600],
            'path' => 'media/banners/',
            'default' => 'default.png',
        ],
        'default' => [
            'path' => 'media/default/',
            'default' => 'default.png',
        ],
        'form-data' => [
            'path' => 'media/form-data/',
            'default' => 'default.png',
        ],
        'dbconfig' => [
            'path' => 'media/default/',
            'default' => 'default.png',
        ],
        'logo' => [
            'size' => [200, 200],
            'path' => 'media/logos/',
            'default' => 'default.png',
        ],
        'private' => [
            'files' => [
                'path' => 'files/',
                'secure' => false,
            ],
            'images' => [
                'path' => 'files/images/',
                'default' => 'default.png',
                'secure' => true,
            ],
            'videos' => [
                'path' => 'files/videos/',
                'secure' => true,
            ],
        ],
    ],
    'image_sizes' => [
        'xs' => '431',
        'sm' => '431',
        'md' => '694',
        'lg' => '720',
        'xl' => '1080',
        'xs-square' => '431x431',
        'sm-square' => '431x431',
        'md-square' => '694x694',
        'lg-square' => '720x720',
        'xl-square' => '1080x1080',
    ],
    'file_route_secure_middleware' => 'window_auth',
    'responsive_image_route' => 'media/images/responsive/{file}/{size}',
    'file_route_secure' => 'secure/media/files/{file}',
    'file_route_open' => 'media/files/{file}',
    'symlinks' => [
        public_path('avatars') => storage_path('app/public/avatars'),
        public_path('media') => storage_path('app/public/media'),
    ],
];
