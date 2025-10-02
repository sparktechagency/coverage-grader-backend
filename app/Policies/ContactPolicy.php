<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContactPolicy
{

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Contact $contract): bool
    {
        return $user->hasRole('admin');
    }


    // /**
    //  * Determine whether the user can update the model.
    //  */
    // public function update(User $user, Contract $contract): bool
    // {
    //     return false;
    // }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Contact $contract): bool
    {
        return $user->hasRole('admin');
    }

}
