<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin 
                            {--name= : The admin user name}
                            {--email= : The admin user email}
                            {--password= : The admin user password}
                            {--employee-id= : The admin user employee ID}
                            {--department= : The admin user department}
                            {--position= : The admin user position}
                            {--role=admin : The admin role (admin or super_admin)}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user for the ACME CSR Platform';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('ACME CSR Platform - Admin User Creation');
        $this->line('==========================================');

        try {
            // Collect user data
            $userData = $this->collectUserData();

            // Validate the data
            $this->validateUserData($userData);

            // Check if user already exists
            if ($this->userExists($userData['email'], $userData['employee_id'])) {
                return Command::FAILURE;
            }

            // Show confirmation
            if (!$this->option('force') && !$this->confirmCreation($userData)) {
                $this->info('Admin user creation cancelled.');
                return Command::SUCCESS;
            }

            // Create the admin user
            $user = $this->createAdminUser($userData);

            $this->displaySuccess($user);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error creating admin user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Collect user data from options or prompts.
     */
    protected function collectUserData(): array
    {
        return [
            'name' => $this->option('name') ?: $this->ask('Admin user full name'),
            'email' => $this->option('email') ?: $this->ask('Admin user email'),
            'password' => $this->option('password') ?: $this->secret('Admin user password'),
            'employee_id' => $this->option('employee-id') ?: $this->ask('Employee ID', $this->generateEmployeeId()),
            'department' => $this->option('department') ?: $this->ask('Department', 'IT'),
            'position' => $this->option('position') ?: $this->ask('Position', 'System Administrator'),
            'role' => $this->option('role'),
        ];
    }

    /**
     * Validate user data.
     */
    protected function validateUserData(array $data): void
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::min(8)],
            'employee_id' => ['required', 'string', 'max:50', 'unique:users'],
            'department' => ['required', 'string', 'max:100'],
            'position' => ['required', 'string', 'max:100'],
            'role' => ['required', 'in:admin,super_admin'],
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->line('  • ' . $error);
            }
            throw new \InvalidArgumentException('Invalid user data provided');
        }
    }

    /**
     * Check if user already exists.
     */
    protected function userExists(string $email, string $employeeId): bool
    {
        $existingUserByEmail = User::where('email', $email)->first();
        $existingUserByEmployeeId = User::where('employee_id', $employeeId)->first();

        if ($existingUserByEmail) {
            $this->error("User with email '{$email}' already exists.");
            return true;
        }

        if ($existingUserByEmployeeId) {
            $this->error("User with employee ID '{$employeeId}' already exists.");
            return true;
        }

        return false;
    }

    /**
     * Show confirmation prompt.
     */
    protected function confirmCreation(array $userData): bool
    {
        $this->line('');
        $this->info('Admin User Details:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $userData['name']],
                ['Email', $userData['email']],
                ['Employee ID', $userData['employee_id']],
                ['Department', $userData['department']],
                ['Position', $userData['position']],
                ['Role', ucfirst(str_replace('_', ' ', $userData['role']))],
                ['Password', str_repeat('*', strlen($userData['password']))],
            ]
        );

        return $this->confirm('Do you want to create this admin user?');
    }

    /**
     * Create the admin user.
     */
    protected function createAdminUser(array $userData): User
    {
        $this->info('Creating admin user...');

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'employee_id' => $userData['employee_id'],
            'department' => $userData['department'],
            'position' => $userData['position'],
            'role' => $userData['role'],
            'is_active' => true,
            'email_verified_at' => now(),
            'total_donated' => 0,
            'campaigns_created' => 0,
        ]);

        return $user;
    }

    /**
     * Display success message.
     */
    protected function displaySuccess(User $user): void
    {
        $this->line('');
        $this->info('✅ Admin user created successfully!');
        $this->line('');
        
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Employee ID', $user->employee_id],
                ['Role', ucfirst(str_replace('_', ' ', $user->role))],
                ['Status', $user->is_active ? 'Active' : 'Inactive'],
                ['Created At', $user->created_at->format('Y-m-d H:i:s')],
            ]
        );

        $this->line('');
        $this->info('🔑 Login Credentials:');
        $this->line("   Email: {$user->email}");
        $this->line("   Password: [as provided]");
        $this->line('');
        $this->warn('⚠️  Please ensure to change the password after first login for security.');
        $this->line('');
        $this->info('🌐 You can now login at: http://localhost:8080');
    }

    /**
     * Generate a unique employee ID.
     */
    protected function generateEmployeeId(): string
    {
        do {
            $employeeId = 'ADM' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        } while (User::where('employee_id', $employeeId)->exists());

        return $employeeId;
    }
} 