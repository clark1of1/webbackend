<?php

return [

    'paths' => [
        'api/*',
        'login',
        'register',
        'logout',
        'sanctum/csrf-cookie'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://webfrontend-bay.vercel.app/'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
