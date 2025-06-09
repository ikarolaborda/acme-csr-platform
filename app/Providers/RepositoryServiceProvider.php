<?php

namespace App\Providers;

use App\Contracts\CampaignRepositoryInterface;
use App\Contracts\DonationRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\Cache\CampaignCacheRepository;
use App\Repositories\Cache\DonationCacheRepository;
use App\Repositories\Cache\UserCacheRepository;
use App\Repositories\Eloquent\CampaignRepository;
use App\Repositories\Eloquent\DonationRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Eloquent repositories
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository($app->make('App\Models\User'));
        });

        $this->app->bind(CampaignRepository::class, function ($app) {
            return new CampaignRepository($app->make('App\Models\Campaign'));
        });

        $this->app->bind(DonationRepository::class, function ($app) {
            return new DonationRepository($app->make('App\Models\Donation'));
        });

        // Bind interfaces to cache implementations
        $this->app->bind(UserRepositoryInterface::class, UserCacheRepository::class);
        $this->app->bind(CampaignRepositoryInterface::class, CampaignCacheRepository::class);
        $this->app->bind(DonationRepositoryInterface::class, DonationCacheRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 