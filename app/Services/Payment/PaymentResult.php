<?php

namespace App\Services\Payment;

class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $transactionId = null,
        public readonly ?string $message = null,
        public readonly ?array $data = null,
        public readonly ?string $errorCode = null
    ) {}

    public static function success(string $transactionId, ?array $data = null): self
    {
        return new self(
            success: true,
            transactionId: $transactionId,
            data: $data
        );
    }

    public static function failure(string $message, ?string $errorCode = null, ?array $data = null): self
    {
        return new self(
            success: false,
            message: $message,
            errorCode: $errorCode,
            data: $data
        );
    }
} 