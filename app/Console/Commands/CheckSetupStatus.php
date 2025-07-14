<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;

class CheckSetupStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check application setup status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📊 Application Setup Status');
        $this->info('=============================');

        $isInitialized = Setting::get('app_initialized', false);
        $managerData = Setting::getManagerData();

        // App initialization status
        $this->info('App Initialized: ' . ($isInitialized ? '✅ Yes' : '❌ No'));

        // Manager configuration status
        $managerConfigured = Setting::isManagerConfigured();
        $this->info('Manager Configured: ' . ($managerConfigured ? '✅ Yes' : '❌ No'));

        if ($managerConfigured) {
            $this->info('Manager Details:');
            $this->info("  Name: {$managerData['name']}");
            $this->info("  Address: {$managerData['address_street']}, {$managerData['address_postal_code']} {$managerData['address_city']}");
            if ($managerData['nip']) $this->info("  NIP: {$managerData['nip']}");
            if ($managerData['regon']) $this->info("  REGON: {$managerData['regon']}");
        }

        // User count
        $userCount = User::count();
        $this->info("Users: {$userCount}");

        // Communities count
        $communityCount = \App\Models\Community::count();
        $this->info("Communities: {$communityCount}");

        // Settings by category
        $categories = Setting::getCategories();
        $this->info('Settings by category:');
        foreach ($categories as $key => $label) {
            $count = Setting::where('category', $key)->count();
            $this->info("  {$label}: {$count} settings");
        }

        if (!$isInitialized || !$managerConfigured) {
            $this->warn('⚠️  Setup incomplete. Run "php artisan app:setup" to complete setup.');
        } else {
            $this->info('✅ Application is fully configured and ready to use!');
        }

        return 0;
    }
}