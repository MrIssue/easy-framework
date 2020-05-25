<?php
return [
    'name' => env('APP_NAME', 'dp'),

    'env' => env('APP_ENV', 'production'),

    'timezone' => 'Asia/Shanghai',

    'slug' => env('APP_SLUG', 'dp'),

    'log' => env('APP_LOG', 'single'),

    'log_max_files' => env('APP_LOG_NUM', 10),

    'debug' => env('APP_DEBUG', false),

    'async' => 'app.async.list', //异步队列名称

    'key' => env('APP_KEY'),

    'url' => env('APP_URL')
];
