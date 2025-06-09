<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Services\PaymentGateways\MockPaymentGateway;
use Illuminate\Support\Manager;

class PaymentGatewayManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return config('services.payment.default', 'mock');
    }

    /**
     * Create the mock payment driver.
     */
    protected function createMockDriver(): PaymentGatewayInterface
    {
        return new MockPaymentGateway();
    }

    /**
     * Create the credit card payment driver.
     */
    protected function createCreditCardDriver(): PaymentGatewayInterface
    {
        // This would be implemented when the actual payment provider is chosen
        return new MockPaymentGateway();
    }

    /**
     * Create the debit card payment driver.
     */
    protected function createDebitCardDriver(): PaymentGatewayInterface
    {
        // This would be implemented when the actual payment provider is chosen
        return new MockPaymentGateway();
    }

    /**
     * Create the PayPal payment driver.
     */
    protected function createPaypalDriver(): PaymentGatewayInterface
    {
        // This would be implemented when the actual payment provider is chosen
        return new MockPaymentGateway();
    }

    /**
     * Create the bank transfer payment driver.
     */
    protected function createBankTransferDriver(): PaymentGatewayInterface
    {
        // This would be implemented when the actual payment provider is chosen
        return new MockPaymentGateway();
    }
}