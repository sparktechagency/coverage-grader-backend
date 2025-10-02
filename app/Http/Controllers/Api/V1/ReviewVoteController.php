<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InsuranceProvider;
use App\Models\Review;
use App\Models\ReviewVote;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReviewVoteController extends Controller
{
     public function index(InsuranceProvider $provider)
    {
        
        $perPage = request()->input('per_page', 15);
        $reviews = $provider->reviews()
            ->with('user')
            ->addSelect([

                'upvotes' => ReviewVote::select(DB::raw('count(*)'))
                    ->whereColumn('review_id', 'reviews.id')
                    ->where('vote', 1),

                'downvotes' => ReviewVote::select(DB::raw('count(*)'))
                    ->whereColumn('review_id', 'reviews.id')
                    ->where('vote', -1),
            ])
            ->orderByRaw('(upvotes - downvotes) DESC')
            ->paginate($perPage);

        return response_success('Reviews fetched successfully', $reviews);
    }

    public function vote(Request $request, Review $review)
    {
        $request->validate([
            'vote' => 'required|in:1,-1',
        ]);

        $user = $request->user();

        $review->votes()->updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'vote' => $request->vote,
            ]
        );

        return response_success('Vote recorded successfully');
    }

}
