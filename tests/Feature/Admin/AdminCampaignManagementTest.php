<?php

namespace Tests\Feature\Admin;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCampaignManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_admin_can_enable_campaign(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft'
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/enable", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Campaign enabled successfully']);

        $campaign->refresh();
        $this->assertEquals('active', $campaign->status);
        $this->assertEquals($admin->id, $campaign->approved_by);
        $this->assertNotNull($campaign->approved_at);
    }

    public function test_admin_can_disable_campaign(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'status' => 'active'
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/disable", [
            'reason' => 'Violates community guidelines'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Campaign disabled successfully']);

        $campaign->refresh();
        $this->assertEquals('cancelled', $campaign->status);
        $this->assertEquals('Violates community guidelines', $campaign->rejection_reason);
    }

    public function test_admin_can_approve_pending_campaign(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/approve", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Campaign approved successfully']);

        $campaign->refresh();
        $this->assertEquals('active', $campaign->status);
        $this->assertEquals($admin->id, $campaign->approved_by);
        $this->assertNotNull($campaign->approved_at);
    }

    public function test_admin_can_reject_pending_campaign(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/reject", [
            'reason' => 'Insufficient project details'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Campaign rejected successfully']);

        $campaign->refresh();
        $this->assertEquals('draft', $campaign->status);
        $this->assertEquals('Insufficient project details', $campaign->rejection_reason);
    }

    public function test_admin_can_toggle_featured_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'is_featured' => false
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/toggle-featured", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Campaign featured successfully']);

        $campaign->refresh();
        $this->assertTrue($campaign->is_featured);

        // Toggle again
        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/toggle-featured", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Campaign unfeatured successfully']);

        $campaign->refresh();
        $this->assertFalse($campaign->is_featured);
    }

    public function test_admin_can_bulk_update_campaign_statuses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaigns = Campaign::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'draft'
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson('/api/v1/admin/campaigns/bulk-update-status', [
            'campaign_ids' => $campaigns->pluck('id')->toArray(),
            'status' => 'active',
            'reason' => 'Bulk activation'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['updated_count' => 3]);

        foreach ($campaigns as $campaign) {
            $campaign->refresh();
            $this->assertEquals('active', $campaign->status);
            $this->assertEquals($admin->id, $campaign->approved_by);
            $this->assertNotNull($campaign->approved_at);
        }
    }

    public function test_admin_can_get_campaign_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        
        Campaign::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Campaign::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        Campaign::factory()->create(['user_id' => $user->id, 'status' => 'draft']);

        $token = auth('api')->login($admin);

        $response = $this->getJson('/api/v1/admin/campaigns/statistics', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_campaigns',
                'by_status',
                'pending_approval',
                'featured_campaigns',
                'expired_campaigns',
                'success_rate',
                'recent_activity'
            ]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $token = auth('api')->login($user);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/enable", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_enable_campaign_with_invalid_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
            'end_date' => now()->subDay() // Expired campaign
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/enable", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Campaign cannot be enabled. Please ensure it has proper details and valid dates.']);
    }

    public function test_can_only_approve_pending_campaigns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'status' => 'active' // Not pending
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/approve", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Only pending campaigns can be approved']);
    }

    public function test_can_only_reject_pending_campaigns(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'status' => 'active' // Not pending
        ]);

        $token = auth('api')->login($admin);

        $response = $this->postJson("/api/v1/admin/campaigns/{$campaign->id}/reject", [
            'reason' => 'Test reason'
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Only pending campaigns can be rejected']);
    }
} 