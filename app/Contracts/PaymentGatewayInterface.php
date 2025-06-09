<?php

namespace App\Contracts;

use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResponseDTO;

interface PaymentGatewayInterface
{
    /**
     * Process a payment.
     */
    public function processPayment(PaymentRequestDTO $request): PaymentResponseDTO;

    /**
     * Verify a payment.
     */
    public function verifyPayment(string $transactionId): PaymentResponseDTO;

    /**
     * Refund a payment.
     */
    public function refundPayment(string $transactionId, float $amount = null): PaymentResponseDTO;

    /**
     * Get payment status.
     */
    public function getPaymentStatus(string $transactionId): string;

    /**
     * Get supported payment methods.
     */
    public function getSupportedMethods(): array;

    /**
     * Get gateway name.
     */
    public function getName(): string;

    /**
     * Check if gateway is available.
     */
    public function isAvailable(): bool;
} 