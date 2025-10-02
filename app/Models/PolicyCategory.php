<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Sluggable;

class PolicyCategory extends Model
{
    use Sluggable;
    protected $guarded = ['id'];

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


    //add image url accessor
    public function getLogoUrlAttribute($value)
    {
        return $value ? Storage::disk('public')->url($value) : null;
    }

    //**Define any relationships or custom methods here
    //relationship with providers
    public function providers()
    {
        return $this->belongsToMany(InsuranceProvider::class, 'provider_policy_junction', 'policy_category_id', 'provider_id');
    }


}
