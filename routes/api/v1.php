<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CampaignController;
use App\Http\Controllers\Api\V1\DonationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\CampaignController as AdminCampaignController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('jwt.auth')->group(function () {
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::get('/donations', [ProfileController::class, 'donations']);
        Route::get('/campaigns', [ProfileController::class, 'campaigns']);
    });

    // Campaigns
    Route::prefix('campaigns')->group(function () {
        Route::get('/', [CampaignController::class, 'index']);
        Route::post('/', [CampaignController::class, 'store']);
        Route::get('/my-campaigns', [CampaignController::class, 'userCampaigns']);
        Route::get('/featured', [CampaignController::class, 'featured']);
        Route::get('/trending', [CampaignController::class, 'trending']);
        Route::get('/ending-soon', [CampaignController::class, 'endingSoon']);
        Route::get('/{slug}', [CampaignController::class, 'show']);
        Route::put('/{id}', [CampaignController::class, 'update']);
        Route::delete('/{id}', [CampaignController::class, 'destroy']);
    });

    // Donations
    Route::prefix('donations')->group(function () {
        Route::get('/', [DonationController::class, 'index']);
        Route::post('/', [DonationController::class, 'store']);
        Route::get('/my-donations', [DonationController::class, 'userDonations']);
        Route::get('/recent', [DonationController::class, 'recent']);
        Route::get('/statistics', [DonationController::class, 'statistics']);
        Route::get('/campaign/{campaignId}/summary', [DonationController::class, 'campaignSummary']);
        Route::get('/{donationNumber}/receipt', [DonationController::class, 'receipt']);
        Route::get('/{donationNumber}', [DonationController::class, 'show']);
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        
        // Campaign management
        Route::prefix('campaigns')->group(function () {
            Route::get('/', [AdminCampaignController::class, 'index']);
            Route::get('/statistics', [AdminCampaignController::class, 'statistics']);
            Route::get('/needs-attention', [AdminCampaignController::class, 'needsAttention']);
            Route::get('/{id}', [AdminCampaignController::class, 'show']);
            Route::post('/{id}/enable', [AdminCampaignController::class, 'enable']);
            Route::post('/{id}/disable', [AdminCampaignController::class, 'disable']);
            Route::post('/{id}/approve', [AdminCampaignController::class, 'approve']);
            Route::post('/{id}/reject', [AdminCampaignController::class, 'reject']);
            Route::post('/{id}/toggle-featured', [AdminCampaignController::class, 'toggleFeatured']);
            Route::patch('/{id}/status', [AdminCampaignController::class, 'updateStatus']);
            Route::post('/bulk-update-status', [AdminCampaignController::class, 'bulkUpdateStatus']);
        });
    });
}); 