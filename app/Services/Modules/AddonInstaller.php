<?php

namespace App\Services\Modules;

use App\Modules\ModuleManifest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class AddonInstaller
{
    public function installFromZip(UploadedFile $zip, bool $force = false): ModuleManifest
    {
        if (strtolower($zip->getClientOriginalExtension()) !== 'zip') {
            throw new RuntimeException('Invalid file type. Please upload a .zip file.');
        }

        $tmpRoot = storage_path('app/addons/tmp/'.Str::random(16));
        File::ensureDirectoryExists($tmpRoot);

        $zipPath = $zip->getRealPath();
        if (! $zipPath) {
            throw new RuntimeException('Uploaded file is not readable.');
        }

        try {
            $archive = new ZipArchive();
            if ($archive->open($zipPath) !== true) {
                throw new RuntimeException('Failed to open zip archive.');
            }

            // Extract to temp and validate later (prevents direct write to app directories).
            $archive->extractTo($tmpRoot);
            $archive->close();

            [$moduleRoot, $manifestPath] = $this->locateManifest($tmpRoot);
            $raw = json_decode((string) File::get($manifestPath), true);
            if (! is_array($raw)) {
                throw new RuntimeException('module.json is invalid JSON.');
            }

        $slug = (string) ($raw['slug'] ?? '');
        if (! preg_match('/^[a-z0-9][a-z0-9_-]{1,62}$/', $slug)) {
            throw new RuntimeException('module.json slug is missing or invalid (use lowercase letters, numbers, _ or -).');
        }

        $directory = (string) ($raw['directory'] ?? basename($moduleRoot));
        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,62}$/', $directory)) {
            // Default to StudlyCase folder if directory not provided.
            $directory = Str::studly($slug);
        }

        $target = base_path('modules'.DIRECTORY_SEPARATOR.$directory);
        if (File::exists($target)) {
            if (! $force) {
                throw new RuntimeException("Module directory already exists: {$directory}. Use force install to overwrite.");
            }
            File::deleteDirectory($target);
        }

        // Safety: ensure we only move a directory we extracted under tmpRoot.
        $realModuleRoot = realpath($moduleRoot);
        $realTmpRoot = realpath($tmpRoot);
        if (! $realModuleRoot || ! $realTmpRoot || ! str_starts_with($realModuleRoot, $realTmpRoot)) {
            throw new RuntimeException('Unsafe module extraction path.');
        }

        File::ensureDirectoryExists(dirname($target));
        if (! File::moveDirectory($moduleRoot, $target)) {
            throw new RuntimeException('Failed to install module into modules directory.');
        }

        // Marker used to allow safe uninstall of zip-installed addons.
        File::put($target.DIRECTORY_SEPARATOR.'.addon-installed', now()->toIso8601String());

            return new ModuleManifest(
            name: (string) ($raw['name'] ?? $directory),
            slug: $slug,
            directory: $directory,
            version: $raw['version'] ?? null,
            description: $raw['description'] ?? null,
            provider: $raw['provider'] ?? null,
            roles: $raw['roles'] ?? [],
            permissions: $raw['permissions'] ?? [],
            navigation: $raw['navigation'] ?? [],
            defaultEnabled: (bool) ($raw['default_enabled'] ?? false),
            allowedPlans: $raw['allowed_plans'] ?? null,
            requiresApiKey: (bool) ($raw['requires_api_key'] ?? false),
            );
        } finally {
            File::deleteDirectory($tmpRoot);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function locateManifest(string $tmpRoot): array
    {
        $rootManifest = $tmpRoot.DIRECTORY_SEPARATOR.'module.json';
        if (File::exists($rootManifest)) {
            return [$tmpRoot, $rootManifest];
        }

        $candidates = File::glob($tmpRoot.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'module.json') ?: [];
        $candidates = array_values(array_filter($candidates, fn ($p) => File::isFile($p)));

        if (count($candidates) !== 1) {
            throw new RuntimeException('Zip must contain exactly one module.json at the root or inside a single folder.');
        }

        $manifestPath = $candidates[0];
        $moduleRoot = dirname($manifestPath);

        return [$moduleRoot, $manifestPath];
    }
}
