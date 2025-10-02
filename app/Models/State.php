<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $guarded = ['id'];

    //**Define any relationships or custom methods here
    //relationship with providers
    public function providers()
    {
        return $this->belongsToMany(InsuranceProvider::class, 'provider_state_junction', 'provider_state_id', 'provider_id');
    }
}
