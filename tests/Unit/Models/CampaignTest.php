<?php

namespace Tests\Unit\Models;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $campaign->user);
        $this->assertEquals($user->id, $campaign->user->id);
    }

    public function test_campaign_has_many_donations(): void
    {
        $campaign = Campaign::factory()->create();
        $donation = Donation::factory()->create(['campaign_id' => $campaign->id]);

        $this->assertTrue($campaign->donations->contains($donation));
    }

    public function test_is_active_scope_returns_active_campaigns(): void
    {
        $activeCampaign = Campaign::factory()->active()->create();
        $inactiveCampaign = Campaign::factory()->create([
            'status' => 'draft',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(30),
        ]);

        $activeCampaigns = Campaign::active()->get();

        $this->assertTrue($activeCampaigns->contains($activeCampaign));
        $this->assertFalse($activeCampaigns->contains($inactiveCampaign));
    }

    public function test_campaign_calculates_progress_percentage(): void
    {
        $campaign = Campaign::factory()->create([
            'goal_amount' => 1000,
            'current_amount' => 250,
        ]);

        $this->assertEquals(25, $campaign->progress_percentage);
    }

    public function test_campaign_knows_if_goal_is_reached(): void
    {
        $campaignNotReached = Campaign::factory()->create([
            'goal_amount' => 1000,
            'current_amount' => 500,
        ]);

        $campaignReached = Campaign::factory()->create([
            'goal_amount' => 1000,
            'current_amount' => 1200,
        ]);

        $this->assertFalse($campaignNotReached->hasReachedGoal());
        $this->assertTrue($campaignReached->hasReachedGoal());
    }

    public function test_campaign_knows_if_it_is_expired(): void
    {
        $activeCampaign = Campaign::factory()->create([
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(10),
        ]);

        $expiredCampaign = Campaign::factory()->create([
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(1),
        ]);

        $this->assertFalse($activeCampaign->hasEnded());
        $this->assertTrue($expiredCampaign->hasEnded());
    }

    public function test_campaign_casts_dates_correctly(): void
    {
        $campaign = Campaign::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $campaign->start_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $campaign->end_date);
    }

    public function test_campaign_fillable_attributes(): void
    {
        $campaign = new Campaign();
        $fillable = $campaign->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('category', $fillable);
        $this->assertContains('goal_amount', $fillable);
        $this->assertContains('current_amount', $fillable);
        $this->assertContains('start_date', $fillable);
        $this->assertContains('end_date', $fillable);
        $this->assertContains('status', $fillable);
    }
} 