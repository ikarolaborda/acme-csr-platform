<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Campaign::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+3 months');
        
        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['environment', 'education', 'health', 'community', 'disaster_relief', 'poverty', 'other']),
            'featured_image' => null,
            'goal_amount' => $this->faker->randomFloat(2, 1000, 50000),
            'current_amount' => $this->faker->randomFloat(2, 0, 25000),
            'donors_count' => $this->faker->numberBetween(0, 100),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $this->faker->randomElement(['draft', 'pending', 'active', 'completed', 'cancelled']),
            'impact_description' => $this->faker->paragraph(),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
        ];
    }

    /**
     * Indicate that the campaign is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
        ]);
    }

    /**
     * Indicate that the campaign is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the campaign has reached its goal.
     */
    public function goalReached(): static
    {
        return $this->state(fn (array $attributes) => [
            'goal_amount' => 10000,
            'current_amount' => 12000,
        ]);
    }

    /**
     * Indicate that the campaign is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDays(1),
        ]);
    }
} 