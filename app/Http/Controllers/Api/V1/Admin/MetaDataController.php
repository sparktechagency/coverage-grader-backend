<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MetaData;
use App\Services\MetaDataService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MetaDataController extends Controller
{
    protected MetaDataService $metaData;
    public function __construct(MetaDataService $metaDataService)
    {
        $this->metaData = $metaDataService;
        // $this->authorizeResource(MetaData::class, 'meta_data');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // dd(MetaData::all());
        $metaDataList = $this->metaData->getAll();
        // if($metaDataList->isEmpty()){
        //     return response_error('No meta data found.', [], 404);
        // }
        return response_success('Meta data list retrieved successfully.', $metaDataList);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'page_name' => [
                'required',
                'string',
                Rule::unique('meta_data', 'page_name')->ignore(
                    MetaData::where('page_name', $request->page_name)->value('id')
                ),
            ],
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

       $metaData = MetaData::updateOrCreate(
            ['page_name' => $request->page_name],
            [
                'title' => $request->title,
                'description' => $request->description,
            ]
        );

        return response_success('Meta data saved successfully.', $metaData);
    }


    /**
     * Display the specified resource.
     */
    public function show(MetaData $metaData)
    {
        return response_success('Meta data retrieved successfully.', $metaData);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MetaData $metaData)
    {
        $metaData->delete();
        return response_success('Meta data deleted successfully.');
    }
}
