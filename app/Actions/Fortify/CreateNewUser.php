<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     * Blocks registration if email was previously used (including deleted accounts).
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        if (User::withTrashed()->where('email', $input['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This email already exists.'],
            ]);
        }

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'role' => ['nullable', 'in:user'],
        ])->validate();

        $userRole = Role::where('slug', 'user')->first();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role_id' => $userRole?->id,
        ]);
    }
}
