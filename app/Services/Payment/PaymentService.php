<?php

namespace App\Services\Payment;

use App\Contracts\PaymentProviderInterface;
use App\Models\Donation;
use App\Services\Payment\Providers\StripePaymentProvider;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private array $providers = [];
    private ?PaymentProviderInterface $defaultProvider = null;

    public function __construct()
    {
        $this->registerProviders();
    }

    /**
     * Register available payment providers.
     */
    private function registerProviders(): void
    {
        // Register Stripe provider
        if (config('payment.stripe.enabled', false)) {
            $stripeProvider = new StripePaymentProvider(
                secretKey: config('payment.stripe.secret_key'),
                publishableKey: config('payment.stripe.publishable_key')
            );
            
            $this->registerProvider($stripeProvider);
            
            if (config('payment.default_provider') === 'stripe') {
                $this->setDefaultProvider($stripeProvider);
            }
        }

        // You can easily add more providers here:
        // PayPal, Square, Braintree, etc.
    }

    /**
     * Register a payment provider.
     */
    public function registerProvider(PaymentProviderInterface $provider): void
    {
        $this->providers[$provider->getProviderName()] = $provider;
        
        if ($this->defaultProvider === null) {
            $this->defaultProvider = $provider;
        }
    }

    /**
     * Set the default payment provider.
     */
    public function setDefaultProvider(PaymentProviderInterface $provider): void
    {
        $this->defaultProvider = $provider;
    }

    /**
     * Get a payment provider by name.
     */
    public function getProvider(string $name): ?PaymentProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    /**
     * Get the default payment provider.
     */
    public function getDefaultProvider(): ?PaymentProviderInterface
    {
        return $this->defaultProvider;
    }

    /**
     * Process a donation payment.
     */
    public function processDonationPayment(Donation $donation, array $paymentData, ?string $providerName = null): PaymentResult
    {
        $provider = $providerName ? $this->getProvider($providerName) : $this->getDefaultProvider();
        
        if (!$provider) {
            Log::error('No payment provider available', [
                'donation_id' => $donation->id,
                'requested_provider' => $providerName
            ]);
            
            return PaymentResult::failure('Payment provider not available', 'no_provider');
        }

        Log::info('Processing donation payment', [
            'donation_id' => $donation->id,
            'provider' => $provider->getProviderName(),
            'amount' => $donation->amount
        ]);

        $result = $provider->processPayment($donation, $paymentData);

        if ($result->success) {
            $donation->markAsCompleted($result->transactionId, $result->data ?? []);
            
            Log::info('Donation payment completed', [
                'donation_id' => $donation->id,
                'transaction_id' => $result->transactionId
            ]);
        } else {
            $donation->markAsFailed($result->message ?? 'Payment processing failed');
            
            Log::warning('Donation payment failed', [
                'donation_id' => $donation->id,
                'error' => $result->message,
                'error_code' => $result->errorCode
            ]);
        }

        return $result;
    }

    /**
     * Create a payment intent for a donation.
     */
    public function createPaymentIntent(Donation $donation, ?string $providerName = null): ?PaymentIntent
    {
        $provider = $providerName ? $this->getProvider($providerName) : $this->getDefaultProvider();
        
        if (!$provider) {
            return null;
        }

        return $provider->createPaymentIntent($donation);
    }

    /**
     * Get all available payment providers.
     */
    public function getAvailableProviders(): array
    {
        return array_map(fn($provider) => [
            'name' => $provider->getProviderName(),
            'supported_methods' => $provider->getSupportedMethods()
        ], $this->providers);
    }

    /**
     * Refund a donation payment.
     */
    public function refundDonation(Donation $donation, float $amount): PaymentResult
    {
        if (!$donation->transaction_id) {
            return PaymentResult::failure('No transaction ID found for refund', 'no_transaction');
        }

        // Try to determine which provider was used based on transaction ID pattern
        $provider = $this->determineProviderFromTransaction($donation->transaction_id);
        
        if (!$provider) {
            return PaymentResult::failure('Cannot determine payment provider for refund', 'unknown_provider');
        }

        return $provider->refundPayment($donation->transaction_id, $amount);
    }

    /**
     * Determine payment provider from transaction ID.
     */
    private function determineProviderFromTransaction(string $transactionId): ?PaymentProviderInterface
    {
        // Simple pattern matching - in production, you might store the provider name with the donation
        if (str_starts_with($transactionId, 'stripe_')) {
            return $this->getProvider('stripe');
        }
        
        // Add more patterns for other providers
        
        return null;
    }
} 