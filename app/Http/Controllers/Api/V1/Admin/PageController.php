<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{

    //list page by type
    public function show(string $type)
    {
        $page = Page::where('type', $type)->first();

        if (!$page) {
            return response_error('page not found',[], 404);
        }

        return response_success('Page content retrieved successfully', $page);
    }


    //create or update page
    public function store(Request $request)
    {

        try {
           $request->validate([
                'type' => 'required|string',
                'content' => 'required|string',
            ]);

            $page = Page::updateOrCreate(
                ['type' => $request->type],
                ['content' => $request->content]
            );

            return response_success('Page updated successfully', $page);
        } catch (\Exception $th) {
            return response_error('Failed to update page: ' . $th->getMessage(), [], 500);
        }
    }

    //page destroy
    public function destroy($type)
    {
        $page = Page::where('type', $type)->first();
        if (!$page) {
            return response_error('Page not found',[], 404);
        }
        $page->delete();
        return response_success('Page deleted successfully', $page);
    }
}
