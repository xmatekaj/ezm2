<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SetupApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup 
                            {--force : Force setup even if already initialized}
                            {--admin-name= : Admin user name}
                            {--admin-email= : Admin user email}
                            {--admin-password= : Admin user password}
                            {--manager-name= : Manager name}
                            {--manager-street= : Manager street address}
                            {--manager-postal= : Manager postal code}
                            {--manager-city= : Manager city}
                            {--manager-nip= : Manager NIP}
                            {--manager-regon= : Manager REGON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the application with initial configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏢 Starting application setup...');

        // Check if already initialized
        if (Setting::get('app_initialized', false) && !$this->option('force')) {
            $this->warn('Application is already initialized. Use --force to override.');
            return 1;
        }

        if ($this->option('force')) {
            $this->warn('Force setup enabled - existing settings will be overridden.');
        }

        // Setup admin user
        $this->setupAdminUser();

        // Setup manager data
        $this->setupManagerData();

        // Mark as initialized
        Setting::set('app_initialized', true, 'boolean');

        $this->info('✅ Application setup completed successfully!');
        $this->info('You can now access the admin panel and start managing communities.');

        return 0;
    }

    private function setupAdminUser(): void
    {
        $this->info('👤 Setting up admin user...');

        $name = $this->option('admin-name') ?? $this->ask('Admin user name', 'Administrator');
        $email = $this->option('admin-email') ?? $this->ask('Admin user email', 'admin@example.com');
        $password = $this->option('admin-password') ?? $this->secret('Admin user password');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  - $error");
            }
            exit(1);
        }

        // Create or update admin user
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        $this->info("✅ Admin user created: {$user->name} ({$user->email})");
    }

    private function setupManagerData(): void
    {
        $this->info('🏢 Setting up manager data...');

        $managerName = $this->option('manager-name') ?? $this->ask('Manager name', 'Zarządca ABC Sp. z o.o.');
        $managerStreet = $this->option('manager-street') ?? $this->ask('Manager street address', 'ul. Zarządu 1');
        $managerPostal = $this->option('manager-postal') ?? $this->ask('Manager postal code', '40-001');
        $managerCity = $this->option('manager-city') ?? $this->ask('Manager city', 'Katowice');
        $managerNip = $this->option('manager-nip') ?? $this->ask('Manager NIP (optional)', '');
        $managerRegon = $this->option('manager-regon') ?? $this->ask('Manager REGON (optional)', '');

        // Validate postal code format
        if (!preg_match('/^\d{2}-\d{3}$/', $managerPostal)) {
            $this->warn("Invalid postal code format. Expected format: XX-XXX");
            $managerPostal = $this->ask('Please enter a valid postal code (XX-XXX)', '40-001');
        }

        // Validate NIP format (optional)
        if ($managerNip && !preg_match('/^\d{3}-\d{3}-\d{2}-\d{2}$/', $managerNip) && !preg_match('/^\d{10}$/', $managerNip)) {
            $this->warn("Invalid NIP format. Expected format: XXX-XXX-XX-XX or XXXXXXXXXX");
            $managerNip = $this->ask('Please enter a valid NIP (optional)', '');
        }

        // Validate REGON format (optional)
        if ($managerRegon && !preg_match('/^\d{9}$/', $managerRegon) && !preg_match('/^\d{14}$/', $managerRegon)) {
            $this->warn("Invalid REGON format. Expected format: 9 or 14 digits");
            $managerRegon = $this->ask('Please enter a valid REGON (optional)', '');
        }

        // Save manager settings
        Setting::set('manager_name', $managerName);
        Setting::set('manager_address_street', $managerStreet);
        Setting::set('manager_address_postal_code', $managerPostal);
        Setting::set('manager_address_city', $managerCity);
        Setting::set('manager_nip', $managerNip);
        Setting::set('manager_regon', $managerRegon);

        $this->info('✅ Manager data configured:');
        $this->info("   Name: {$managerName}");
        $this->info("   Address: {$managerStreet}, {$managerPostal} {$managerCity}");
        if ($managerNip) $this->info("   NIP: {$managerNip}");
        if ($managerRegon) $this->info("   REGON: {$managerRegon}");
    }
}