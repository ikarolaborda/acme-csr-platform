<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Donations\CreateDonation;
use App\Actions\Donations\ProcessDonationPayment;
use App\Contracts\DonationRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Donations\CreateDonationRequest;
use App\Http\Resources\DonationResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DonationController extends Controller
{
    public function __construct(
        protected DonationRepositoryInterface $donationRepository
    ) {}

    /**
     * Display a listing of donations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'campaign_id',
            'status',
            'date_from',
            'date_to',
            'amount_min',
            'amount_max'
        ]);

        // Add user filter for non-admin users
        if (!auth()->user()->isAdmin()) {
            $filters['user_id'] = auth()->id();
        }

        $perPage = $request->input('per_page', 20);
        $donations = $this->donationRepository->paginate($perPage, $filters);

        return DonationResource::collection($donations);
    }

    /**
     * Store a newly created donation.
     */
    public function store(
        CreateDonationRequest $request,
        CreateDonation $createAction,
        ProcessDonationPayment $paymentAction
    ): JsonResponse {
        // Create donation record
        $donation = $createAction->execute($request->validated());

        // Process payment
        $paymentResult = $paymentAction->execute($donation);

        if (!$paymentResult->success) {
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $paymentResult->message,
            ], 422);
        }

        return response()->json([
            'message' => 'Donation created successfully',
            'data' => new DonationResource($donation->fresh()),
        ], 201);
    }

    /**
     * Display the specified donation.
     */
    public function show(string $donationNumber): JsonResponse
    {
        $donation = $this->donationRepository->findByDonationNumber($donationNumber);

        if (!$donation) {
            return response()->json([
                'message' => 'Donation not found'
            ], 404);
        }

        // Check authorization
        if ($donation->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'data' => new DonationResource($donation)
        ]);
    }

    /**
     * Get user's donation history.
     */
    public function userDonations(): AnonymousResourceCollection
    {
        $donations = $this->donationRepository->getUserDonationHistory(auth()->id());

        return DonationResource::collection($donations);
    }

    /**
     * Get recent donations.
     */
    public function recent(): AnonymousResourceCollection
    {
        $donations = $this->donationRepository->getRecent(20);

        return DonationResource::collection($donations);
    }

    /**
     * Get donation statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'campaign_id']);

        if (!auth()->user()->isAdmin()) {
            $filters['user_id'] = auth()->id();
        }

        $statistics = $this->donationRepository->getStatistics($filters);

        return response()->json([
            'data' => $statistics
        ]);
    }

    /**
     * Get campaign donation summary.
     */
    public function campaignSummary(int $campaignId): JsonResponse
    {
        $summary = $this->donationRepository->getCampaignDonationSummary($campaignId);

        return response()->json([
            'data' => $summary
        ]);
    }

    /**
     * Generate and download donation receipt PDF.
     */
    public function receipt(string $donationNumber): Response
    {
        $donation = $this->donationRepository->findByDonationNumber($donationNumber);

        if (!$donation) {
            abort(404, 'Donation not found');
        }

        // Check authorization
        if ($donation->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Load relationships
        $donation->load(['campaign', 'user']);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.donation-receipt', compact('donation'));
        $pdf->setPaper('A4', 'portrait');

        // Generate filename
        $filename = 'donation-receipt-' . $donation->donation_number . '.pdf';

        return $pdf->download($filename);
    }
} 