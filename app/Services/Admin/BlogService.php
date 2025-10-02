<?php

namespace App\Services\Admin;

use App\Services\BaseService;
use App\Models\Blog;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;
use App\Traits\FileUploadTrait;

class BlogService extends BaseService
{
    use FileUploadTrait;
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Blog::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }

    // Define allowed filters
     protected function getAllowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new GlobalSearchFilter, 'title','author_name'),
            'title',
            'author_name',
            AllowedFilter::exact('status'),
        ];
    }

    // Define allowed includes relationships
     protected function getAllowedIncludes(): array
     {
        return [
            'user',
            'policyCategories'
        ];
     }

     // Define allowed sorts
     protected function getAllowedSorts(): array
     {
        return [
            'id',
            'title',
            'created_at',
        ];
     }

     
     //store blog
        public function storeBlog($request, array $data): Blog
        {
            //image handler
            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $this->handleFileUpload($request, 'featured_image', 'blogs', null, null, 85, true);
            }
            $user = auth()->user();
            $data['user_id'] = $user->id;
            return $this->create($data);
        }

    //update blog
    public function updateBlog($blog, $request, array $data): Blog
    {
        //image handler
        if ($request->hasFile('featured_image')) {
            //remove old image
            $this->deleteFile($blog->featured_image);
            //upload new image
            $data['featured_image'] = $this->handleFileUpload($request, 'featured_image', 'blogs', null, null, 85, true);
        }
        return $this->update($blog->id, $data);
    }

    //delete blog
    public function deleteBlog($blog): bool
    {
        //delete featured image
        if ($blog->featured_image) {
            $this->deleteFile($blog->featured_image);
        }
        return $this->delete($blog->id);
    }

    //update blog status
    public function updateBlogStatus($blog, $status): Blog
    {
        $data = [
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ];
        return $this->update($blog->id, $data);
    }

}
