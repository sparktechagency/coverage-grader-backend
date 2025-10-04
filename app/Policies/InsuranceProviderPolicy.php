<?php

namespace App\Policies;

use App\Models\InsuranceProvider;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InsuranceProviderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, InsuranceProvider $insuranceProvider): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InsuranceProvider $insuranceProvider): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InsuranceProvider $insuranceProvider): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InsuranceProvider $insuranceProvider): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InsuranceProvider $insuranceProvider): bool
    {
        return $user->hasRole('admin');
    }

    // Determine whether the user can update sponsorship status of the model.
    public function updateSponsorshipStatus(User $user, InsuranceProvider $insuranceProvider): bool
    {
        return $user->hasRole('admin');
    }
}
