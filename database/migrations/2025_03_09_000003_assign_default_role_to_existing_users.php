<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User', 'is_subadmin' => false]
        );
        User::whereNull('role_id')->update(['role_id' => $userRole->id]);
    }

    public function down(): void
    {
        //
    }
};
