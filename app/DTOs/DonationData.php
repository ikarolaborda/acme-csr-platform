<?php

namespace App\DTOs;

class DonationData
{
    public function __construct(
        public readonly int $campaign_id,
        public readonly int $user_id,
        public readonly float $amount,
        public readonly ?string $donation_number,
        public readonly bool $is_anonymous,
        public readonly ?string $message,
        public readonly ?string $payment_method,
        public readonly ?string $transaction_id,
        public readonly ?string $status,
    ) {}

    /**
     * Create a new instance from an array.
     */
    public static function from(array $data): self
    {
        return new self(
            campaign_id: (int) $data['campaign_id'],
            user_id: (int) $data['user_id'],
            amount: (float) $data['amount'],
            donation_number: $data['donation_number'] ?? null,
            is_anonymous: (bool) ($data['is_anonymous'] ?? false),
            message: $data['message'] ?? null,
            payment_method: $data['payment_method'] ?? null,
            transaction_id: $data['transaction_id'] ?? null,
            status: $data['status'] ?? 'pending',
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'campaign_id' => $this->campaign_id,
            'user_id' => $this->user_id,
            'amount' => $this->amount,
            'donation_number' => $this->donation_number,
            'is_anonymous' => $this->is_anonymous,
            'message' => $this->message,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'status' => $this->status,
        ], fn($value) => !is_null($value));
    }
} 