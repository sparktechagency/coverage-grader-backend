<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Http\Resources\Admin\BlogResource;
use App\Models\Blog;
use App\Services\Admin\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected BlogService $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = $this->blogService->getAll();
        return BlogResource::collection($blogs);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogRequest $request)
    {
        $validated = $request->validated();
        //store blog
        $blog = $this->blogService->storeBlog($request, $validated);
        return response_success('Blog created successfully.', $blog, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        return new BlogResource($blog->load(['user', 'policyCategories']));
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(BlogRequest $request, Blog $blog)
    {
        $validated = $request->validated();
        //update blog
        $blog = $this->blogService->updateBlog($blog, $request, $validated);
        return response_success('Blog updated successfully.', $blog);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        $this->blogService->deleteBlog($blog); 
        return response_success('Blog deleted successfully');
    }

    //update status
    public function updateStatus(Request $request, Blog $blog)
    {
        $request->validate([
            'status' => 'required|in:draft,published',
        ]);
        $blog = $this->blogService->updateBlogStatus($blog, $request->status);
        return response_success('Blog status updated successfully.', new BlogResource($blog));
    }
}
