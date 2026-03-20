<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Local Country Code
    |--------------------------------------------------------------------------
    |
    | This is the fallback ISO 3166-1 alpha-2 country code used when a phone
    | number starts with 0 (local format) and no explicit fallback is passed.
    | Set this to your app's primary country to avoid passing it on every call.
    |
    | Example: 'MA' for Morocco, 'FR' for France, 'US' for United States.
    |
    */

    'default_country' => env('PHONE_COUNTRY_DEFAULT', 'XX'),

    /*
    |--------------------------------------------------------------------------
    | Unknown Country Code
    |--------------------------------------------------------------------------
    |
    | The code returned when a number cannot be resolved to any country.
    | Defaults to 'XX' (standard designation for unknown/unassigned).
    |
    */

    'unknown_code' => 'XX',

];
