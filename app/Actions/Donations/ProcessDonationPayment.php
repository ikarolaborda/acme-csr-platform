<?php

namespace App\Actions\Donations;

use App\Contracts\DonationRepositoryInterface;
use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentRequestDTO;
use App\DTOs\PaymentResponseDTO;
use App\Models\Donation;
use App\Services\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDonationPayment
{
    public function __construct(
        protected DonationRepositoryInterface $donationRepository,
        protected PaymentGatewayManager $paymentManager
    ) {}

    /**
     * Execute the payment processing action.
     */
    public function execute(Donation $donation): PaymentResponseDTO
    {
        try {
            return DB::transaction(function () use ($donation) {
                // Get the appropriate payment gateway
                $gateway = $this->paymentManager->driver($donation->payment_method);

                // Create payment request DTO
                $paymentRequest = new PaymentRequestDTO(
                    amount: $donation->amount,
                    currency: $donation->currency,
                    donationId: (string) $donation->id,
                    userId: (string) $donation->user_id,
                    campaignId: (string) $donation->campaign_id,
                    paymentMethod: $donation->payment_method,
                    metadata: [
                        'donation_number' => $donation->donation_number,
                        'campaign_title' => $donation->campaign->title,
                    ]
                );

                // Process the payment
                $response = $gateway->processPayment($paymentRequest);

                if ($response->success) {
                    // Mark donation as completed
                    $this->donationRepository->markAsCompleted(
                        $donation->id,
                        $response->transactionId,
                        $response->data
                    );
                } else {
                    // Mark donation as failed
                    $this->donationRepository->markAsFailed(
                        $donation->id,
                        $response->message
                    );
                }

                return $response;
            });
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage()
            ]);

            // Mark donation as failed
            $this->donationRepository->markAsFailed(
                $donation->id,
                'Payment processing error: ' . $e->getMessage()
            );

            return PaymentResponseDTO::failed(
                'Payment processing failed. Please try again.',
                'PROCESSING_ERROR'
            );
        }
    }
} 