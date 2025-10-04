<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class InsuranceProvider extends Model
{
    use Sluggable, LogsActivity;

    protected $guarded = ['id'];

    protected $casts = [
        'is_sponsored' => 'boolean',
        'pros' => 'array',
        'cons' => 'array',
        'price' => 'decimal:2',
    ];

    //create unique slug
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    //**Define any relationships or custom methods here
    //relationship with policies
    public function policyCategories()
    {
       return $this->belongsToMany(PolicyCategory::class, 'provider_policy_junction', 'provider_id', 'policy_category_id');
    }

    //relationship with states
    public function states()
    {
        return $this->belongsToMany(State::class, 'provider_state_junction', 'provider_id', 'provider_state_id');
    }

    //relationship with reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'provider_id');
    }


    //add image url accessor
    public function getLogoUrlAttribute($value)
    {
        return $value ? Storage::disk('public')->url($value) : null;
    }

    /**
     * Get the formatted score string with average and grade.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function avgGrade(): Attribute
    {
        return Attribute::make(
            get: function () {
                $average = $this->avg_overall_rating;
                $grade = '';
                if ($average >= 4.8) {
                    $grade = 'A+';
                } elseif ($average >= 4.5) {
                    $grade = 'A';
                } elseif ($average >= 4.0) {
                    $grade = 'A-';
                } elseif ($average >= 3.8) {
                    $grade = 'B+';
                } elseif ($average >= 3.5) {
                    $grade = 'B';
                } elseif ($average >= 3.0) {
                    $grade = 'B-';
                } elseif ($average >= 2.5) {
                    $grade = 'C';
                } else {
                    $grade = 'D';
                }
                return $grade;
            }
        );
    }

     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status','is_sponsored','logo_url']) //customize the fields you want to log
            ->logOnlyDirty() //log only the changed fields
            ->setDescriptionForEvent(fn(string $eventName) => "Insurance Provider has been {$eventName}")
            ->useLogName('insurance_provider_activity');
    }
}
