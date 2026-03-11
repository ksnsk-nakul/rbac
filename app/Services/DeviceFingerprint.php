<?php

namespace App\Services;

use Illuminate\Http\Request;

class DeviceFingerprint
{
    public static function fromRequest(Request $request, int $userId): string
    {
        return hash('sha256', implode('|', [
            $userId,
            $request->userAgent() ?? '',
            $request->ip() ?? '',
        ]));
    }
}
