<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Domains
    |--------------------------------------------------------------------------
    |
    | Every teacher gets "<slug>.<base_domain>". The central site (sign-up,
    | Super Admin) lives on "<central_subdomain>.<base_domain>". No domain is
    | hard-coded anywhere else in the app.
    |
    */

    'base_domain' => env('APP_BASE_DOMAIN', 'nexoralms.localhost'),

    'central_subdomain' => env('APP_CENTRAL_SUBDOMAIN', 'app'),

    'scheme' => env('APP_SCHEME', 'http'),

    'reserved_slugs' => [
        'app', 'www', 'api', 'admin', 'mail', 'static', 'assets', 'cdn',
        'help', 'support', 'status', 'billing', 'manage', 'dashboard', 'nexora',
    ],

    /*
    |--------------------------------------------------------------------------
    | Time
    |--------------------------------------------------------------------------
    |
    | The database stores UTC (config/app.php timezone). Everything shown to
    | people, and every "which month is it" decision, uses this timezone.
    |
    */

    'display_timezone' => 'Asia/Colombo',

    /*
    |--------------------------------------------------------------------------
    | Trial
    |--------------------------------------------------------------------------
    */

    'trial_days' => 30,

];
