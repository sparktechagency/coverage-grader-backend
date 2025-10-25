<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    protected $fillable = [
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
    ];
}
