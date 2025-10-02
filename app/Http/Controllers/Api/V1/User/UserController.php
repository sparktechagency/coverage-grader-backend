<?php

namespace App\Http\Controllers\APi\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\State;
use App\Services\Admin\FaqService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected FaqService $faqService;
    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }
     //list page by type
    public function getAllPages(string $type)
    {
        $page = Page::where('type', $type)->first();

        if (!$page) {
            return response_error('page not found',[], 404);
        }

        return response_success('Page content retrieved successfully', $page);
    }

    //get faqs
    public function getFaqs()
    {
        $faqs = $this->faqService->getAll();
        if($faqs->isEmpty()){
            return response_error('No faqs found',[], 404);
        }
        return response_success('Faqs retrieved successfully', $faqs);
    }

    //get all states
    public function getAllStates()
    {
        $states = State::all(['id', 'name', 'code']);
        if($states->isEmpty()){
            return response_error('No states found',[], 404);
        }
        return response_success('States retrieved successfully', $states);
    }
}
