<?php

namespace App\Policies;

use App\Models\MetaData;
use App\Models\User;

class MetaDataPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, MetaData $metaData): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, MetaData $metaData): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, MetaData $metaData): bool
    {
        return $user->hasRole('admin');
    }
}
