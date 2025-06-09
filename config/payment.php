<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment provider that will be used
    | for processing donations. You can switch between providers without
    | changing application code.
    |
    */

    'default_provider' => env('PAYMENT_DEFAULT_PROVIDER', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe payment processing.
    |
    */

    'stripe' => [
        'enabled' => env('STRIPE_ENABLED', true),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PayPal Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PayPal payment processing.
    | (Implementation can be added when needed)
    |
    */

    'paypal' => [
        'enabled' => env('PAYPAL_ENABLED', false),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Default currency and supported currencies for the platform.
    |
    */

    'currency' => [
        'default' => env('PAYMENT_DEFAULT_CURRENCY', 'USD'),
        'supported' => ['USD', 'EUR', 'GBP', 'CAD'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum Donation Amount
    |--------------------------------------------------------------------------
    |
    | The minimum amount that can be donated (to avoid processing fees
    | exceeding the donation amount).
    |
    */

    'minimum_amount' => env('PAYMENT_MINIMUM_AMOUNT', 5.00),

    /*
    |--------------------------------------------------------------------------
    | Maximum Donation Amount
    |--------------------------------------------------------------------------
    |
    | The maximum amount that can be donated in a single transaction.
    |
    */

    'maximum_amount' => env('PAYMENT_MAXIMUM_AMOUNT', 10000.00),
]; 