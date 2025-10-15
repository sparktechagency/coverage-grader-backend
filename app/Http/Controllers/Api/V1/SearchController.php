<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InsuranceProviderResource;
use App\Models\InsuranceProvider;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $provider = InsuranceProvider::where('name', 'LIKE', "%{$query}%")
            ->orWhere('slug', 'LIKE', "%{$query}%")
            ->paginate($perPage);
        if($provider->isEmpty()){
            return response_error('No insurance providers found', [], 404);
        }
        return InsuranceProviderResource::collection($provider);
    }
}
