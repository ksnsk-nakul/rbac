<?php

namespace App\Modules;

class ModuleManifest
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $version,
        public ?string $description,
        public ?string $provider,
        public array $permissions,
        public array $navigation,
        public bool $defaultEnabled,
    ) {
    }
}
