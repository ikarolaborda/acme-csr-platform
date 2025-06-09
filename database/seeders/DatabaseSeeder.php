<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@acme.com',
            'password' => Hash::make('password'),
            'employee_id' => 'EMP001',
            'department' => 'IT',
            'position' => 'System Administrator',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Create regular users
        $users = User::factory(20)->create();

        // Create campaigns
        $categories = ['environment', 'education', 'health', 'community', 'disaster_relief', 'poverty'];
        $statuses = ['active', 'completed', 'pending'];

        foreach ($users->random(10) as $user) {
            $campaignsCount = rand(1, 3);
            
            for ($i = 0; $i < $campaignsCount; $i++) {
                $goalAmount = rand(5000, 50000);
                $currentAmount = rand(0, $goalAmount);
                $startDate = now()->subDays(rand(0, 30));
                $endDate = $startDate->copy()->addDays(rand(30, 90));
                
                $campaign = Campaign::create([
                    'title' => fake()->sentence(4),
                    'slug' => fake()->unique()->slug(3),
                    'description' => fake()->paragraphs(3, true),
                    'short_description' => fake()->paragraph(),
                    'user_id' => $user->id,
                    'category' => fake()->randomElement($categories),
                    'goal_amount' => $goalAmount,
                    'current_amount' => $currentAmount,
                    'donors_count' => 0,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => fake()->randomElement($statuses),
                    'is_featured' => fake()->boolean(20),
                    'views_count' => rand(0, 1000),
                    'impact_description' => fake()->paragraph(),
                    'approved_at' => now(),
                    'approved_by' => $admin->id,
                ]);

                // Create donations for active campaigns
                if ($campaign->status === 'active' && $currentAmount > 0) {
                    $donorsCount = rand(5, 20);
                    $donationAmounts = $this->distributeDonations($currentAmount, $donorsCount);
                    
                    foreach ($donationAmounts as $amount) {
                        $donor = $users->random();
                        
                        Donation::create([
                            'user_id' => $donor->id,
                            'campaign_id' => $campaign->id,
                            'amount' => $amount,
                            'currency' => 'USD',
                            'status' => 'completed',
                            'payment_method' => fake()->randomElement(['credit_card', 'debit_card', 'paypal']),
                            'transaction_id' => 'TXN-' . fake()->unique()->numerify('########'),
                            'is_anonymous' => fake()->boolean(20),
                            'message' => fake()->boolean(50) ? fake()->sentence() : null,
                            'paid_at' => fake()->dateTimeBetween($startDate, 'now'),
                        ]);
                    }
                    
                    // Update campaign totals
                    $campaign->updateTotals();
                }
            }
        }

        // Update user statistics
        foreach ($users as $user) {
            $user->update([
                'total_donated' => $user->donations()->where('status', 'completed')->sum('amount'),
                'campaigns_created' => $user->campaigns()->count(),
            ]);
        }
    }

    /**
     * Distribute total amount among donors
     */
    private function distributeDonations($total, $count)
    {
        $amounts = [];
        $remaining = $total;
        
        for ($i = 0; $i < $count - 1; $i++) {
            $max = min($remaining * 0.3, 1000); // Max 30% of remaining or $1000
            $amount = rand(10, max(10, $max));
            $amounts[] = $amount;
            $remaining -= $amount;
        }
        
        // Last donation gets the remaining amount
        if ($remaining > 0) {
            $amounts[] = $remaining;
        }
        
        return $amounts;
    }
}
