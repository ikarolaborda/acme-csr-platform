<?php

namespace App\DTOs;

readonly class PaymentRequestDTO
{
    public function __construct(
        public float $amount,
        public string $currency,
        public string $donationId,
        public string $userId,
        public string $campaignId,
        public string $paymentMethod,
        public array $metadata = []
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: $data['amount'],
            currency: $data['currency'] ?? 'USD',
            donationId: $data['donation_id'],
            userId: $data['user_id'],
            campaignId: $data['campaign_id'],
            paymentMethod: $data['payment_method'],
            metadata: $data['metadata'] ?? []
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'donation_id' => $this->donationId,
            'user_id' => $this->userId,
            'campaign_id' => $this->campaignId,
            'payment_method' => $this->paymentMethod,
            'metadata' => $this->metadata,
        ];
    }
} 