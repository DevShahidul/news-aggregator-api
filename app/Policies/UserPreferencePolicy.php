<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPreferencePolicy
{
    use HandlesAuthorization;

    public function view(User $user, UserPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }

    public function delete(User $user, UserPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }
} 