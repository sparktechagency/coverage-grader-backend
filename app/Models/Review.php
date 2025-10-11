<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'scores' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(InsuranceProvider::class, 'provider_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

     public function votes()
    {
        return $this->hasMany(ReviewVote::class);
    }


    protected function averageScore(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $scores = json_decode($attributes['scores'] ?? '[]', true);

                if (empty($scores) || !is_array($scores)) {
                    return 0;
                }
                $totalScore = array_sum($scores);
                $numberOfScores = count($scores);
                return $numberOfScores > 0 ? round($totalScore / $numberOfScores, 1) : 0;
            }
        );
    }

    /**
     * Get the formatted score string with average and grade.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function formattedScore(): Attribute
    {
        return Attribute::make(
            get: function () {
                $average = $this->average_score;
                $grade = '';
                if ($average >= 4.5) {
                    $grade = 'A';
                } elseif ($average >= 3.5) {
                    $grade = 'B';
                } elseif ($average >= 2.5) {
                    $grade = 'C';
                } else {
                    $grade = 'D';
                }
                return "{$average}/5 ({$grade})";
            }
        );
    }


}



