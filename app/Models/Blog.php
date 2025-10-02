<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
    use Sluggable;
    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    //create unique slug
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    //route model binding by slug
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    


    //**Relationship block */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    //policy category
    public function policyCategories()
    {
        return $this->belongsTo(PolicyCategory::class, 'category_id');
    }

    //**End Relationship block */

    //get featured image url
    public function getFeaturedImageAttribute($value): ?string
    {
        return $value ? Storage::disk('public')->url($value) : null;
    }
}
