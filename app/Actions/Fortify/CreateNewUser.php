<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
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
        $role = $this->resolveRole($input['role'] ?? null);

        if (! $role) {
            throw ValidationException::withMessages([
                'role' => ['Invalid role selection.'],
            ]);
        }

        if (User::withTrashed()
            ->where('email', $input['email'])
            ->where('role_id', $role->id)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'email' => ['This email already exists.'],
            ]);
        }

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'route'),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where('role_id', $role->id),
            ],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role_id' => $role->id,
        ]);

        if (\Illuminate\Support\Facades\Schema::hasTable('organizations')) {
            $orgName = trim($input['organization_name'] ?? '') ?: "{$user->name}'s Workspace";
            $orgSlug = Str::slug($orgName).'-'.Str::lower(Str::random(6));

            $organization = Organization::create([
                'name' => $orgName,
                'slug' => $orgSlug,
                'owner_user_id' => $user->id,
                'plan_id' => null,
            ]);

            OrganizationUser::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'org_role' => 'owner',
                'status' => 'active',
            ]);

            $user->forceFill(['current_organization_id' => $organization->id])->save();
        }

        return $user;
    }

    private function resolveRole(?string $roleInput): ?Role
    {
        if (! $roleInput) {
            return Role::where('is_default', true)->first();
        }

        return Role::where('route', $roleInput)->orWhere('slug', $roleInput)->first();
    }
}
