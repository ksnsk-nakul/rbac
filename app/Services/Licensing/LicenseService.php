<?php

namespace App\Services\Licensing;

use App\Models\License;
use App\Models\LicenseInstance;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class LicenseService
{
    public function validateAndTouch(?string $key, string $instanceId, ?string $appUrl = null): bool
    {
        if (! Schema::hasTable('licenses') || ! Schema::hasTable('license_instances')) {
            return true; // self-hosted installs may skip licensing tables
        }

        $key = $key ?: $this->keyFromSettingsOrEnv();
        if (! $key) {
            return false;
        }

        $license = License::where('key', $key)->first();
        if (! $license || $license->status !== 'active') {
            return false;
        }

        if ($license->valid_until && $license->valid_until->isPast()) {
            return false;
        }

        $instance = LicenseInstance::firstOrCreate(
            ['instance_id' => $instanceId],
            [
                'license_id' => $license->id,
                'app_url' => $appUrl,
                'registered_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        if ((int) $instance->license_id !== (int) $license->id) {
            return false;
        }

        $instance->forceFill([
            'last_seen_at' => now(),
            'app_url' => $appUrl ?? $instance->app_url,
        ])->save();

        $count = LicenseInstance::where('license_id', $license->id)->count();
        return $count <= (int) $license->max_instances;
    }

    private function keyFromSettingsOrEnv(): ?string
    {
        $key = (string) env('APP_LICENSE_KEY', '');

        if (Schema::hasTable('settings')) {
            $key = (string) Setting::getValueSafe('license.key', $key);
        }

        return $key !== '' ? $key : null;
    }
}
