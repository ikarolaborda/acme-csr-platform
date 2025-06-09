<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResponseDTO;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Process a payment.
     */
    public function processPayment(PaymentRequestDTO $request): PaymentResponseDTO
    {
        // Simulate processing delay
        usleep(500000); // 0.5 seconds

        // Simulate different scenarios based on amount
        if ($request->amount > 10000) {
            return PaymentResponseDTO::failed(
                'Transaction limit exceeded',
                'LIMIT_EXCEEDED'
            );
        }

        if ($request->amount == 666) {
            return PaymentResponseDTO::failed(
                'Payment declined by bank',
                'DECLINED'
            );
        }

        // Simulate successful payment
        $transactionId = 'MOCK-' . strtoupper(Str::random(16));

        return PaymentResponseDTO::success(
            $transactionId,
            'completed',
            [
                'gateway' => 'mock',
                'timestamp' => now()->toIsoString(),
                'reference' => 'REF-' . strtoupper(Str::random(8)),
            ]
        );
    }

    /**
     * Verify a payment.
     */
    public function verifyPayment(string $transactionId): PaymentResponseDTO
    {
        // Simulate verification
        if (str_starts_with($transactionId, 'MOCK-')) {
            return PaymentResponseDTO::success(
                $transactionId,
                'completed',
                [
                    'verified' => true,
                    'verified_at' => now()->toIsoString(),
                ]
            );
        }

        return PaymentResponseDTO::failed(
            'Transaction not found',
            'NOT_FOUND'
        );
    }

    /**
     * Refund a payment.
     */
    public function refundPayment(string $transactionId, float $amount = null): PaymentResponseDTO
    {
        // Simulate refund
        if (str_starts_with($transactionId, 'MOCK-')) {
            $refundId = 'REFUND-' . strtoupper(Str::random(16));

            return PaymentResponseDTO::success(
                $refundId,
                'refunded',
                [
                    'original_transaction' => $transactionId,
                    'refund_amount' => $amount,
                    'refunded_at' => now()->toIsoString(),
                ]
            );
        }

        return PaymentResponseDTO::failed(
            'Transaction not found',
            'NOT_FOUND'
        );
    }

    /**
     * Get payment status.
     */
    public function getPaymentStatus(string $transactionId): string
    {
        if (str_starts_with($transactionId, 'MOCK-')) {
            return 'completed';
        }

        if (str_starts_with($transactionId, 'REFUND-')) {
            return 'refunded';
        }

        return 'unknown';
    }

    /**
     * Get supported payment methods.
     */
    public function getSupportedMethods(): array
    {
        return ['credit_card', 'debit_card', 'paypal', 'bank_transfer'];
    }

    /**
     * Get gateway name.
     */
    public function getName(): string
    {
        return 'Mock Payment Gateway';
    }

    /**
     * Check if gateway is available.
     */
    public function isAvailable(): bool
    {
        return true;
    }
} 