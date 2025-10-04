<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsuranceProvider;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{



    public function index()
    {

        $stats = [
            'total_users' => $this->getTotalUsers(),
            'active_providers' => $this->getActiveProviders(),
            'reviews_pending' => $this->getPendingReviews(),
            'active_users' => $this->getActiveUsers(),
        ];
        $reviewsPerWeek = $this->getReviewsSubmittedPerWeek();


        $reviewsDistribution = $this->getReviewsDistribution();

        return response()->json([
            'stats' => $stats,
            'reviewsPerWeek' => $reviewsPerWeek,
            'reviewsDistribution' => $reviewsDistribution,
        ]);
    }

    /**
     * Helper function to calculate percentage change.
     * This avoids repeating the same logic.
     */
    private function calculatePercentageChange($currentValue, $previousValue)
    {
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100 : 0;
        }

        $difference = $currentValue - $previousValue;
        $percentageChange = ($difference / $previousValue) * 100;

        return round($percentageChange);
    }

    private function getTotalUsers()
    {

        $currentMonthCount = User::where('created_at', '>=', now()->subDays(30))->count();
        $previousMonthCount = User::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])->count();

        return [
            'total' => User::count(),
            'percentage_change' => $this->calculatePercentageChange($currentMonthCount, $previousMonthCount)
        ];
    }

    private function getActiveProviders()
    {
        $currentMonthCount = InsuranceProvider::where('status', 'active')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $previousMonthCount = InsuranceProvider::where('status', 'active')
            ->whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->count();

        return [
            'total' => InsuranceProvider::where('status', 'active')->count(),
            'percentage_change' => $this->calculatePercentageChange($currentMonthCount, $previousMonthCount)
        ];
    }

    private function getPendingReviews()
    {
        return Review::where('status', 'pending')->count();
    }

    private function getActiveUsers()
    {
        $currentMonthCount = User::where('last_login_at', '>=', now()->subDays(30))->count();
        $previousMonthCount = User::whereBetween('last_login_at', [now()->subDays(60), now()->subDays(30)])->count();

        return [
            'total' => $currentMonthCount,
            'percentage_change' => $this->calculatePercentageChange($currentMonthCount, $previousMonthCount)
        ];
    }


    private function getReviewsSubmittedPerWeek()
    {
        $endDate = now();
        $startDate = now()->subWeeks(5)->startOfWeek();

        $reviews = Review::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('count(id) as count'), DB::raw('WEEK(created_at, 1) as week')) // WEEK mode 1 (Monday is first day)
            ->groupBy('week')
            ->pluck('count', 'week')
            ->all();

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $week = now()->subWeeks($i);
            $weekNumber = $week->weekOfYear;

            $labels[] = 'Week ' . (6 - $i);
            $data[] = $reviews[$weekNumber] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getReviewsDistribution()
    {

        return DB::table('reviews')
            ->where('reviews.status', 'approved')
            ->join('insurance_providers', 'reviews.provider_id', '=', 'insurance_providers.id')
            ->join('provider_policy_junction', 'insurance_providers.id', '=', 'provider_policy_junction.provider_id')
            ->join('policy_categories', 'provider_policy_junction.policy_category_id', '=', 'policy_categories.id')
            ->select('policy_categories.name', DB::raw('count(reviews.id) as total'))
            ->groupBy('policy_categories.name')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'value' => $item->total,
                ];
            });
    }


    public function recentActivity()
    {

        $adminId = Auth::id();

        $activities = Activity::with('causer')
                              ->where(function ($query) use ($adminId) {
                                  $query->where('description', 'like', '%submitted a review for%')
                                        ->orWhere(function($q) use ($adminId) {
                                            $q->where('causer_type', 'App\Models\User')
                                              ->where('causer_id', $adminId);
                                        });
                              })
                              ->latest()
                              ->take(10)
                              ->get();


        $formattedActivities = $activities->map(function ($activity) {

            $causerName = $activity->causer ? $activity->causer->name : 'A System User';

            return [
                'description' => $causerName . ' ' . $activity->description,
                'time' => $activity->created_at->diffForHumans(),
            ];
        });

        return response_success('Recent activities retrieved successfully.', $formattedActivities);
    }
}
