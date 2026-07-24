<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | Set to false for production
    |
    */
    'test_mode' => env('MYFATOORAH_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Test API URL
    |--------------------------------------------------------------------------
    |
    | MyFatoorah test API endpoint
    |
    */
    'test_url' => env('MYFATOORAH_TEST_URL', 'https://apitest.myfatoorah.com'),

    /*
    |--------------------------------------------------------------------------
    | Country Mode
    |--------------------------------------------------------------------------
    |
    | Determines the country endpoint
    | Options: KWT (Kuwait), SAU (Saudi Arabia), ARE (UAE), QAT (Qatar), BHR (Bahrain), OMN (Oman), JOD (Jordan), EGY (Egypt)
    |
    */
    'country_mode' => env('MYFATOORAH_COUNTRY_MODE', 'KWT'),

    /*
    |--------------------------------------------------------------------------
    | API Token
    |--------------------------------------------------------------------------
    |
    | Your MyFatoorah API token
    | IMPORTANT: This MUST be set in your .env file as MYFATOORAH_API_KEY
    | Do NOT use the default value - get your API key from MyFatoorah dashboard
    |
    */
    'api_key' => env('MYFATOORAH_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('MYFATOORAH_CURRENCY', 'KWD'),

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    */
    'success_url' => env('MYFATOORAH_SUCCESS_URL', '/order/payment/success'),
    'error_url' => env('MYFATOORAH_ERROR_URL', '/order/payment/failed'),
];

















