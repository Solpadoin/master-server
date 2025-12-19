<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupResetCommand extends Command
{
    protected $signature = 'setup:reset
                            {--force : Skip confirmation prompt}
                            {--keep-users : Keep non-admin users}';

    protected $description = 'Reset the setup wizard to start fresh';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will reset the setup wizard and delete admin users. Continue?')) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        $this->components->info('Resetting setup wizard...');

        // Delete setup complete file
        $setupFile = storage_path('app/.setup_complete');
        if (File::exists($setupFile)) {
            File::delete($setupFile);
            $this->components->task('Remove setup complete flag', fn () => true);
        } else {
            $this->components->task('Setup complete flag not found', fn () => true);
        }

        // Delete users
        if ($this->option('keep-users')) {
            $count = User::where('is_admin', true)->count();
            User::where('is_admin', true)->delete();
            $this->components->task("Delete admin users ({$count} removed)", fn () => true);
        } else {
            $count = User::count();
            User::truncate();
            $this->components->task("Delete all users ({$count} removed)", fn () => true);
        }

        $this->newLine();
        $this->components->success('Setup wizard has been reset. Visit /setup to start again.');

        return self::SUCCESS;
    }
}
