<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PolicyCategoryResource;
use App\Http\Resources\InsuranceProviderResource;
use App\Models\PolicyCategory;
use App\Services\Admin\PolicyCategoryService;
use App\Services\InsuranceProviderService;


class PolicyManagementController extends Controller
{
    protected PolicyCategoryService $policyCategoryService;
    protected InsuranceProviderService $insuranceProviderService;
    public function __construct(PolicyCategoryService $policyCategoryService, InsuranceProviderService $insuranceProviderService)
    {
        $this->policyCategoryService = $policyCategoryService;
        $this->insuranceProviderService = $insuranceProviderService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $queryCallback = function ($query) {
            $query->where('status', 'active');
        };
        $categories = $this->policyCategoryService->getAll($queryCallback);
        if($categories->isEmpty()){
            return response_error('No policy categories found', [], 404);
        }
        return PolicyCategoryResource::collection($categories);
    }

    /**
     * Display the specified resource.
     */
    public function show(PolicyCategory $policy)
    {
        if($policy->status != 'active'){
            return response_error('Policy category not found', [], 404);
        }
        //top insurance providers
        $topProvicers = $this->insuranceProviderService->getAll(
            function ($query) {
                $query->orderBy('avg_overall_rating', 'desc');
            }
        )->take(4);
        // return new PolicyCategoryResource($policy);
        $data = [
            'category' => new PolicyCategoryResource($policy),
            'top_providers' => $topProvicers->isEmpty() ? [] : InsuranceProviderResource::collection($topProvicers),
        ];
        return response_success('Policy category details', $data);
    }

}
