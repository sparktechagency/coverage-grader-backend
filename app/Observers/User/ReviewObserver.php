<?php

namespace App\Observers\User;

use App\Models\Review;
use App\Observers\BaseObserver;
use App\Services\ReviewService;
use Illuminate\Database\Eloquent\Model;

class ReviewObserver extends BaseObserver
{
    /**
     * Handle events after all transactions are committed.
     *
     * @var bool
     */
    public $afterCommit = true;

    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Model $review): void
    {
        parent::updated($review);
        if ($review->isDirty('status')) {
            $this->reviewService->recalculateProviderRatings($review->provider_id);
        }
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Model $review): void
    {
        parent::deleted($review);
        if ($review->getOriginal('status') === 'approved') {
            $this->reviewService->recalculateProviderRatings($review->provider_id);
        }
    }

}
