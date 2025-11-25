<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Review;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReviewNotification;

class ReviewService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Review::class;

    protected bool $cachePerUser = true;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }

    // Define allowed filters
    protected function getAllowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new GlobalSearchFilter, 'user.first_name', 'user.last_name'),
            'user.first_name',
            AllowedFilter::exact('status'),
        ];
    }

    // Define allowed includes relationships
    protected function getAllowedIncludes(): array
    {
        return [
            'user',
            'provider',
            'state',
        ];
    }

    // Define allowed sorts
    protected function getAllowedSorts(): array
    {
        return [
            'id',
            'user.first_name',
            'created_at',
            'overall_rating'
        ];
    }


    //create review
    public function createReview(array $data, User $user): Review
    {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $review = $this->create($data);
            // dd($review);
            //sent notification to admin
            // Notify admin about the new review
            Notification::send(User::role('admin')->get(), new ReviewNotification($review));
            activity()->causedBy($user)
                ->performedOn($review)
                ->withProperties(['attributes' => $data])
                ->log($user->first_name . ' ' . $user->last_name . ' submitted a review for '.$review->provider->name);
            return $review;
        });
    }

    //update review
    public function updateReview($review, array $data): Review
    {
        return DB::transaction(function () use ($review, $data) {
            return $this->update($review->id, $data);
        });
    }

    /**
     * Recalculate and update the average ratings for a specific provider.
     *
     * @param int $providerId
     */
    public function recalculateProviderRatings(int $providerId): void
    {
        $approvedReviews = Review::where('provider_id', $providerId)
            ->where('status', 'approved');

        $reviewCount = $approvedReviews->count();
        $avgOverallRating = $approvedReviews->avg('overall_rating') ?? 0;

        $avgCategoryScores = null;

        if ($reviewCount > 0) {
            $avgCategoryScores = DB::table('reviews')
                ->where('provider_id', $providerId)
                ->where('status', 'approved')
                ->select(
                    DB::raw('ROUND(AVG(JSON_EXTRACT(scores, "$.claims")), 1) as claims'),
                    DB::raw('ROUND(AVG(JSON_EXTRACT(scores, "$.service")), 1) as service'),
                    DB::raw('ROUND(AVG(JSON_EXTRACT(scores, "$.pricing")), 1) as pricing'),
                    DB::raw('ROUND(AVG(JSON_EXTRACT(scores, "$.coverage")), 1) as coverage'),
                    DB::raw('ROUND(AVG(JSON_EXTRACT(scores, "$.trust")), 1) as trust')
                )->first();
        } else {
            $avgCategoryScores = [
                'claims' => 0,
                'service' => 0,
                'pricing' => 0,
                'coverage' => 0,
                'trust' => 0,
            ];
        }
        DB::table('insurance_providers')->where('id', $providerId)->update([
            'review_count' => $reviewCount,
            'avg_overall_rating' => $avgOverallRating,
            'avg_scores' => json_encode($avgCategoryScores),
        ]);
    }
}
