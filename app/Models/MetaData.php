<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class MetaData extends Model
{
    use HasFactory;

    protected $table = 'meta_data';

    public function getRouteKeyName(): string
    {
        return 'page_name';
    }

    protected $fillable = [
        'page_name',
        'title',
        'description',
    ];
}
