<?php

namespace App\Contracts;

use App\Models\Donation;
use App\Services\Payment\PaymentResult;
use App\Services\Payment\PaymentIntent;
use App\Services\Payment\PaymentVerification;

interface PaymentProviderInterface
{
    /**
     * Process a payment for a donation.
     */
    public function processPayment(Donation $donation, array $paymentData): PaymentResult;

    /**
     * Create a payment intent/session.
     */
    public function createPaymentIntent(Donation $donation): PaymentIntent;

    /**
     * Verify a payment callback/webhook.
     */
    public function verifyPayment(string $transactionId, array $data): PaymentVerification;

    /**
     * Refund a payment.
     */
    public function refundPayment(string $transactionId, float $amount): PaymentResult;

    /**
     * Get payment provider name.
     */
    public function getProviderName(): string;

    /**
     * Get supported payment methods.
     */
    public function getSupportedMethods(): array;
} 