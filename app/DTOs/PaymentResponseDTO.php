<?php

namespace App\DTOs;

readonly class PaymentResponseDTO
{
    public function __construct(
        public bool $success,
        public ?string $transactionId,
        public ?string $status,
        public ?string $message,
        public array $data = [],
        public ?string $errorCode = null
    ) {}

    /**
     * Create a successful response.
     */
    public static function success(string $transactionId, string $status = 'completed', array $data = []): self
    {
        return new self(
            success: true,
            transactionId: $transactionId,
            status: $status,
            message: 'Payment processed successfully',
            data: $data
        );
    }

    /**
     * Create a failed response.
     */
    public static function failed(string $message, ?string $errorCode = null, array $data = []): self
    {
        return new self(
            success: false,
            transactionId: null,
            status: 'failed',
            message: $message,
            data: $data,
            errorCode: $errorCode
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'transaction_id' => $this->transactionId,
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
            'error_code' => $this->errorCode,
        ];
    }
} 