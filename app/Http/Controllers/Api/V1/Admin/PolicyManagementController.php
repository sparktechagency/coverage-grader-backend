<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PoliceCategoryRequest;
use App\Http\Resources\Admin\PolicyCategoryResource;
use App\Models\PolicyCategory;
use App\Services\Admin\PolicyCategoryService;
use Illuminate\Http\Request;

class PolicyManagementController extends Controller
{
    protected PolicyCategoryService $policyCategoryService;

    public function __construct(PolicyCategoryService $policyCategoryService)
    {
        $this->policyCategoryService = $policyCategoryService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->policyCategoryService->getAll();
        if($categories->isEmpty()){
            return response_error('No policy categories found', [], 404);
        }
        return PolicyCategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PoliceCategoryRequest $request)
    {
        $validated = $request->validated();
        $category = $this->policyCategoryService->storeCategory($request, $validated);
        return response_success('Policy category created successfully.', $category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PolicyCategory $policy)
    {
        return new PolicyCategoryResource($policy);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PoliceCategoryRequest $request, PolicyCategory $policy)
    {
        $validated = $request->validated();
        // return $policy;
        $category = $this->policyCategoryService->updateCategory($policy, $request, $validated);
        return response_success('Policy category updated successfully.', $category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PolicyCategory $policy )
    {
        $this->policyCategoryService->deleteCategory($policy);
        return response_success('Policy category deleted successfully');
    }
}
