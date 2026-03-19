<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class AppInstall extends Command
{
    protected $signature = 'app:install
        {--fresh : Drop all tables and re-run all migrations}
        {--seed : Run database seeders}
        {--force : Force the operation to run in production}';

    protected $description = 'Install the application (env, key, migrate, seed, storage link, cache clear).';

    public function handle(): int
    {
        $this->ensureEnvFile();

        $this->callSilent('key:generate', ['--force' => true]);

        if ($this->option('fresh')) {
            $this->call('migrate:fresh', [
                '--seed' => (bool) $this->option('seed'),
                '--force' => (bool) $this->option('force'),
            ]);
        } else {
            $this->call('migrate', [
                '--force' => (bool) $this->option('force'),
            ]);

            if ($this->option('seed')) {
                $this->call('db:seed', [
                    '--force' => (bool) $this->option('force'),
                ]);
            }
        }

        $this->callSilent('storage:link');
        $this->callSilent('optimize:clear');

        $this->info('Install complete.');
        $this->line('Next: `npm install && npm run dev` (or `npm run build` for production assets).');

        return Command::SUCCESS;
    }

    private function ensureEnvFile(): void
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (File::exists($envPath)) {
            return;
        }

        if (! File::exists($examplePath)) {
            $this->warn('No .env or .env.example found. Skipping env creation.');
            return;
        }

        File::copy($examplePath, $envPath);
        $this->info('Created .env from .env.example.');
    }
}
