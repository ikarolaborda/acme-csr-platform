<?php

namespace App\Services\Payment\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\Donation;
use App\Services\Payment\PaymentResult;
use App\Services\Payment\PaymentIntent;
use App\Services\Payment\PaymentVerification;
use Illuminate\Support\Facades\Log;

class StripePaymentProvider implements PaymentProviderInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $publishableKey
    ) {}

    public function processPayment(Donation $donation, array $paymentData): PaymentResult
    {
        try {
            // Simulate Stripe payment processing
            // In a real implementation, you would use the Stripe SDK here
            
            $transactionId = 'stripe_' . uniqid();
            
            // Simulate payment processing logic
            if ($this->simulatePaymentSuccess($donation, $paymentData)) {
                Log::info('Stripe payment processed successfully', [
                    'donation_id' => $donation->id,
                    'transaction_id' => $transactionId,
                    'amount' => $donation->amount
                ]);

                return PaymentResult::success($transactionId, [
                    'provider' => 'stripe',
                    'amount' => $donation->amount,
                    'currency' => $donation->currency
                ]);
            }

            return PaymentResult::failure('Payment failed', 'payment_declined');

        } catch (\Exception $e) {
            Log::error('Stripe payment error', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage()
            ]);

            return PaymentResult::failure($e->getMessage(), 'payment_error');
        }
    }

    public function createPaymentIntent(Donation $donation): PaymentIntent
    {
        // Simulate creating a Stripe Payment Intent
        $intentId = 'pi_' . uniqid();
        $clientSecret = $intentId . '_secret_' . uniqid();

        return new PaymentIntent(
            id: $intentId,
            clientSecret: $clientSecret,
            amount: $donation->amount,
            currency: $donation->currency,
            status: 'requires_payment_method',
            metadata: [
                'donation_id' => $donation->id,
                'campaign_id' => $donation->campaign_id
            ]
        );
    }

    public function verifyPayment(string $transactionId, array $data): PaymentVerification
    {
        // Simulate Stripe payment verification
        // In a real implementation, you would verify with Stripe's API
        
        if (str_starts_with($transactionId, 'stripe_')) {
            return PaymentVerification::valid(
                transactionId: $transactionId,
                status: 'succeeded',
                amount: $data['amount'] ?? 0,
                data: $data
            );
        }

        return PaymentVerification::invalid($data);
    }

    public function refundPayment(string $transactionId, float $amount): PaymentResult
    {
        try {
            // Simulate Stripe refund
            $refundId = 're_' . uniqid();
            
            Log::info('Stripe refund processed', [
                'transaction_id' => $transactionId,
                'refund_id' => $refundId,
                'amount' => $amount
            ]);

            return PaymentResult::success($refundId, [
                'original_transaction' => $transactionId,
                'refund_amount' => $amount
            ]);

        } catch (\Exception $e) {
            return PaymentResult::failure($e->getMessage(), 'refund_error');
        }
    }

    public function getProviderName(): string
    {
        return 'stripe';
    }

    public function getSupportedMethods(): array
    {
        return [
            'card',
            'apple_pay',
            'google_pay',
            'sepa_debit',
            'ach_debit'
        ];
    }

    /**
     * Simulate payment success for demonstration purposes.
     */
    private function simulatePaymentSuccess(Donation $donation, array $paymentData): bool
    {
        // Simple simulation: payments under $1000 succeed
        return $donation->amount < 1000;
    }
} 