<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics.
     */
    public function index(): JsonResponse
    {
        $stats = [
            'overview' => $this->getOverviewStats(),
            'campaigns' => $this->getCampaignStats(),
            'donations' => $this->getDonationStats(),
            'users' => $this->getUserStats(),
            'recent_campaigns' => $this->getRecentCampaigns(),
            'recent_donations' => $this->getRecentDonations(),
            'top_donors' => $this->getTopDonors(),
            'category_breakdown' => $this->getCategoryBreakdown(),
        ];

        return response()->json($stats);
    }

    /**
     * Get overview statistics.
     */
    protected function getOverviewStats(): array
    {
        return [
            'total_campaigns' => Campaign::count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'total_raised' => Donation::where('status', 'completed')->sum('amount'),
            'total_donors' => Donation::where('status', 'completed')->distinct('user_id')->count('user_id'),
        ];
    }

    /**
     * Get campaign statistics.
     */
    protected function getCampaignStats(): array
    {
        return [
            'by_status' => Campaign::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status'),
            'success_rate' => Campaign::where('current_amount', '>=', DB::raw('goal_amount'))->count() / max(Campaign::count(), 1) * 100,
            'average_goal' => Campaign::avg('goal_amount'),
            'average_raised' => Campaign::avg('current_amount'),
        ];
    }

    /**
     * Get donation statistics.
     */
    protected function getDonationStats(): array
    {
        $donations = Donation::where('status', 'completed');
        
        return [
            'total_count' => $donations->count(),
            'total_amount' => $donations->sum('amount'),
            'average_donation' => $donations->avg('amount'),
            'monthly_trend' => $this->getMonthlyDonationTrend(),
        ];
    }

    /**
     * Get user statistics.
     */
    protected function getUserStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('last_login_at', '>=', now()->subDays(30))->count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
            'users_by_role' => User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->pluck('count', 'role'),
        ];
    }

    /**
     * Get recent campaigns.
     */
    protected function getRecentCampaigns(): array
    {
        return Campaign::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'status' => $campaign->status,
                    'goal_amount' => $campaign->goal_amount,
                    'current_amount' => $campaign->current_amount,
                    'creator' => $campaign->user->name,
                    'created_at' => $campaign->created_at->toDateTimeString(),
                ];
            })
            ->toArray();
    }

    /**
     * Get recent donations.
     */
    protected function getRecentDonations(): array
    {
        return Donation::with(['user', 'campaign'])
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($donation) {
                return [
                    'id' => $donation->id,
                    'amount' => $donation->amount,
                    'donor' => $donation->is_anonymous ? 'Anonymous' : $donation->user->name,
                    'campaign' => $donation->campaign->title,
                    'created_at' => $donation->created_at->toDateTimeString(),
                ];
            })
            ->toArray();
    }

    /**
     * Get top donors.
     */
    protected function getTopDonors(): array
    {
        return User::select('users.id', 'users.name', DB::raw('SUM(donations.amount) as total_donated'))
            ->join('donations', 'users.id', '=', 'donations.user_id')
            ->where('donations.status', 'completed')
            ->where('donations.is_anonymous', false)
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_donated')
            ->take(5)
            ->get()
            ->toArray();
    }

    /**
     * Get category breakdown.
     */
    protected function getCategoryBreakdown(): array
    {
        return Campaign::select('category', DB::raw('count(*) as count'), DB::raw('SUM(current_amount) as total_raised'))
            ->groupBy('category')
            ->get()
            ->toArray();
    }

    /**
     * Get monthly donation trend.
     */
    protected function getMonthlyDonationTrend(): array
    {
        // Use database-agnostic date formatting
        if (DB::connection()->getDriverName() === 'mysql') {
            return Donation::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total')
                )
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->toArray();
        } else {
            return Donation::select(
                    DB::raw('strftime("%Y-%m", created_at) as month'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(amount) as total')
                )
                ->where('status', 'completed')
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->toArray();
        }
    }
} 