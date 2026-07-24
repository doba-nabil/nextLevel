<?php

return [
    'merchant_id' => env('BOOKEEY_MERCHANT_ID'),
    'secret_key' => env('BOOKEEY_SECRET_KEY'),
    'sub_merchant_id' => env('BOOKEEY_SUB_MERCHANT_ID'),
    'payment_url' => env('BOOKEEY_PAYMENT_URL'),
    'status_url' => env('BOOKEEY_STATUS_URL'),
    'base_url' => env('BOOKEEY_BASE_URL'),
    'success_url' => env('BOOKEEY_SUCCESS_URL'),
    'error_url' => env('BOOKEEY_ERROR_URL'),
    'currency' => env('BOOKEEY_CURRENCY', 'KWD'),
];
