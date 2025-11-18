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
        $perPage = $request->input('per_page', 10);


        $providersQuery = InsuranceProvider::query();


        if ($request->filled('search')) {
            $query = $request->input('search');


            $providersQuery->where(function ($q) use ($query) {


                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('slug', 'LIKE', "%{$query}%");


                $q->orWhereHas('states', function ($stateQuery) use ($query) {
                    $stateQuery->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('code', 'LIKE', "%{$query}%");
                });


                $q->orWhereHas('policyCategories', function ($categoryQuery) use ($query) {
                    $categoryQuery->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('slug', 'LIKE', "%{$query}%");
                });
            });
        }


        $provider = $providersQuery->paginate($perPage);

        if ($provider->isEmpty()) {
            return response_error('No insurance providers found', [], 404);
        }

        return InsuranceProviderResource::collection($provider);
    }
}
