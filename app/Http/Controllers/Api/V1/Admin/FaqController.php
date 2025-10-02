<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Services\Admin\FaqService;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    protected FaqService $faqService;
    public function __construct(FaqService $faqService)
    {
        $this->faqService = $faqService;
    }

    //list faqs
    public function index()
    {
        $faqs = $this->faqService->getAll();
       if($faqs->isEmpty()){
        return response_error('No FAQs found.', [], 404);
       }
        return response_success('FAQs retrieved successfully.', $faqs);
    }

    //show faq
    public function show($id)
    {
        $faq = $this->faqService->getById($id);
        return response_success('FAQ retrieved successfully.', $faq);
    }
    //create faq
    public function store(FaqRequest $request)
    {
        $faq = $this->faqService->create($request->validated());
        return response_success('FAQ created successfully.', $faq);
    }

    //update faq
    public function update(FaqRequest $request, $id)
    {
        $faq = $this->faqService->update($id, $request->validated());
        return response_success('FAQ updated successfully.', $faq);
    }

    //delete faq
    public function destroy($id)
    {
        $this->faqService->delete($id);
        return response_success('FAQ deleted successfully.', []);
    }
}
