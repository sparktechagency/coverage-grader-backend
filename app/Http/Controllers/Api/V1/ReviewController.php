<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Notifications\ReviewStatusNotification;
use App\Services\ReviewService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ReviewController extends Controller
{
    use AuthorizesRequests;
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('optional.auth')->only(['index']);
        $this->reviewService = $reviewService;
        $this->authorizeResource(Review::class, 'review');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user(); // Get the user. It can be null.

        if ($user && $user->hasRole('admin')) {
            $reviews = $this->reviewService->getAll();
        }
        elseif ($user) {
            $queryCallback = function ($query) use ($user) {
                $query->where('user_id', $user->id);
            };
            $reviews = $this->reviewService->getAll($queryCallback);
        }
        else {
            $queryCallback = function ($query) {
                $query->where('status', 'approved');
            };
            $reviews = $this->reviewService->getAll($queryCallback);
        }

        if ($reviews->isEmpty()) {
            return response_success('No reviews found.', []);
        }

        return ReviewResource::collection($reviews);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewRequest $request)
    {
        $data = $request->validated();
        $review = $this->reviewService->createReview($data, $request->user());

        return response_success('Review submitted successfully.', new ReviewResource($review->load('user', 'provider', 'state')));
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        return response_success('Review retrieved successfully.', new ReviewResource($review->load('user', 'provider', 'state')));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(ReviewRequest $request, Review $review)
    {
        $data = $request->validated();
        $reviewData = $this->reviewService->updateReview($review, $data);
        return response_success('Review updated successfully.', $reviewData);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();
        return response_success('Review deleted successfully.');
    }

    /**
     * update status of review
     */
    public function updateStatus(Request $request, Review $review)
    {
        $request->validate([
            'status' => 'required|in:approved,pending,rejected',
        ]);
        $this->authorize('updateStatus', $review);
        $review->status = $request->input('status');
        $review->save();
        //sent notification to user about status change
        // Notify user about the status change
        Notification::send($review->user, new ReviewStatusNotification($review));
        activity()->causedBy(auth()->user())
            ->performedOn($review)
            ->withProperties(['attributes' => ['status' => $review->status]])
            ->log('Review status updated to "' . $review->status . '" for ' . $review->provider->name);

        return response_success('Review status updated successfully.', new ReviewResource($review));
    }
}
