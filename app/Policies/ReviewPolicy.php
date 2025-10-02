<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{

    public function before(User $user, string $ability): bool|null
    {
        //if user is admin, grant all permissions
        if ($user->hasRole('admin') && $ability !== 'create') {
            return true;
        }

        return null; 
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }


    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * Update status only by admin
     */

    public function updateStatus(User $user): bool
    {
        return $user->hasRole('admin');
    }

}
