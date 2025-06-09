<?php

namespace App\Services\Payment;

class PaymentVerification
{
    public function __construct(
        public readonly bool $isValid,
        public readonly ?string $transactionId = null,
        public readonly ?string $status = null,
        public readonly ?float $amount = null,
        public readonly ?array $data = null
    ) {}

    public static function valid(string $transactionId, string $status, float $amount, ?array $data = null): self
    {
        return new self(
            isValid: true,
            transactionId: $transactionId,
            status: $status,
            amount: $amount,
            data: $data
        );
    }

    public static function invalid(?array $data = null): self
    {
        return new self(
            isValid: false,
            data: $data
        );
    }
} 