<?php

namespace Tests\Feature\Campaigns;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    protected function headers($token = null)
    {
        $headers = ['Accept' => 'application/json'];
        
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }
        
        return $headers;
    }

    protected function login($user = null)
    {
        $user = $user ?: User::factory()->create();
        
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        
        // Debug: Check if login was successful
        if (!$response->isSuccessful()) {
            throw new \Exception('Login failed: ' . $response->getContent());
        }
        
        return $response->json('access_token');
    }

    public function test_authenticated_user_can_list_campaigns(): void
    {
        $token = $this->login();
        Campaign::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/campaigns', $this->headers($token));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'goal_amount',
                        'current_amount',
                        'status',
                    ]
                ]
            ]);
    }

    public function test_authenticated_user_can_create_campaign(): void
    {
        $user = User::factory()->create();
        $token = $this->login($user);

        $campaignData = [
            'title' => 'Test Campaign',
            'description' => 'This is a test campaign description',
            'short_description' => 'Short description',
            'category' => 'environment',
            'goal_amount' => 5000,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
        ];

        $response = $this->postJson('/api/v1/campaigns', $campaignData, $this->headers($token));

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'Test Campaign',
                'category' => 'environment',
            ]);

        $this->assertDatabaseHas('campaigns', [
            'title' => 'Test Campaign',
            'user_id' => $user->id,
        ]);
    }

    public function test_campaign_validation_rules(): void
    {
        $token = $this->login();

        $response = $this->postJson('/api/v1/campaigns', [], $this->headers($token));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'category', 'goal_amount', 'start_date', 'end_date']);
    }

    public function test_user_can_view_campaign_details(): void
    {
        $token = $this->login();
        $campaign = Campaign::factory()->create();

        $response = $this->getJson("/api/v1/campaigns/{$campaign->slug}", $this->headers($token));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => $campaign->title,
                'slug' => $campaign->slug,
            ]);
    }

    public function test_user_can_update_own_campaign(): void
    {
        $user = User::factory()->create();
        $token = $this->login($user);
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Campaign Title',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/v1/campaigns/{$campaign->id}", $updateData, $this->headers($token));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Updated Campaign Title',
            ]);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'title' => 'Updated Campaign Title',
        ]);
    }

    public function test_user_cannot_update_others_campaign(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = $this->login($user);
        
        $campaign = Campaign::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->putJson("/api/v1/campaigns/{$campaign->id}", ['title' => 'Hacked'], $this->headers($token));

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_campaign(): void
    {
        $user = User::factory()->create();
        $token = $this->login($user);
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/campaigns/{$campaign->id}", [], $this->headers($token));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }

    public function test_can_filter_campaigns_by_category(): void
    {
        $token = $this->login();
        Campaign::factory()->create(['category' => 'environment']);
        Campaign::factory()->create(['category' => 'education']);
        Campaign::factory()->create(['category' => 'environment']);

        $response = $this->getJson('/api/v1/campaigns?category=environment', $this->headers($token));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_search_campaigns(): void
    {
        $token = $this->login();
        Campaign::factory()->create(['title' => 'Save the Rainforest']);
        Campaign::factory()->create(['title' => 'Clean Ocean Initiative']);
        Campaign::factory()->create(['title' => 'Education for All']);

        $response = $this->getJson('/api/v1/campaigns?search=ocean', $this->headers($token));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Clean Ocean Initiative']);
    }

    public function test_campaign_with_image_upload(): void
    {
        Storage::fake('public');
        
        $user = User::factory()->create();
        $token = $this->login($user);

        $campaignData = [
            'title' => 'Campaign with Image',
            'description' => 'Description',
            'short_description' => 'Short description',
            'category' => 'community',
            'goal_amount' => 3000,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'image' => UploadedFile::fake()->image('campaign.jpg'),
        ];

        $response = $this->postJson('/api/v1/campaigns', $campaignData, $this->headers($token));

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['featured_image_url']
            ]);

        // Get the created campaign to check the image path
        $campaign = Campaign::where('title', 'Campaign with Image')->first();
        $this->assertNotNull($campaign->featured_image);
        Storage::disk('public')->assertExists($campaign->featured_image);
    }
} 