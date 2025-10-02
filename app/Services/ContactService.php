<?php

namespace App\Services;

use App\Services\BaseService;
use App\Models\Contact;
use Spatie\QueryBuilder\AllowedFilter;
use App\Filters\GlobalSearchFilter;

class ContactService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = Contact::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }

    // Define allowed filters
     protected function getAllowedFilters(): array
    {
        return [
            AllowedFilter::custom('search', new GlobalSearchFilter, 'name','email'),
            'name',
            'email',
            AllowedFilter::exact('status'),
        ];
    }

    // Define allowed includes relationships
     protected function getAllowedIncludes(): array
     {
        return [
            //
        ];
     }

     // Define allowed sorts
     protected function getAllowedSorts(): array
     {
        return [
            'id',
            'name',
            'created_at',
        ];
     }

    // Mark the specified contact message as read.
    public function markAsRead($contact): void
    {
        $contact->read_at = now();
        $contact->save();
    }
}
