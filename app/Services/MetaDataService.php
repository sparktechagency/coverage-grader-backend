<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\MetaData;

class MetaDataService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = MetaData::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }

    // Define allowed filters
     protected function getAllowedFilters(): array
    {
        return [];
    }

    // Define allowed includes relationships
     protected function getAllowedIncludes(): array
     {
        return [];
     }

     // Define allowed sorts
     protected function getAllowedSorts(): array
     {
        return [];
     }
}
