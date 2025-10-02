<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
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
        $queryCallback = function ($query) {
            $query->where('status', 'published');
        };
        $blogs = $this->blogService->getAll($queryCallback);
        if($blogs->isEmpty()){
            return response_error('No blogs found', [], 404);
        }
        return BlogResource::collection($blogs);
    }


    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        if($blog->status != 'published'){
            return response_error('Blog not found', [], 404);
        }
        //related 4 blogs
        $relatedBlogs = $this->blogService->getAll(
            function ($query) use ($blog) {
                $query->where('status', 'published')
                      ->where('id', '!=', $blog->id)
                      ->where('category_id', $blog->category_id)
                      ->inRandomOrder();
            }
        ,false)->take(3);
        $data = [
            'blog' => new BlogResource($blog->load(['user', 'policyCategories'])),
            'related_blogs' => $relatedBlogs->isEmpty() ? [] : BlogResource::collection($relatedBlogs),
        ];
        return response_success('Blog details', $data);
    }

}
