<?php

namespace App\Services\Payment;

class PaymentIntent
{
    public function __construct(
        public readonly string $id,
        public readonly string $clientSecret,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $status,
        public readonly ?array $metadata = null
    ) {}
} 