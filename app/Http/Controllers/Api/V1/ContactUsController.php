<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    protected ContactService $contactService;
    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
        $this->authorizeResource(Contact::class, 'contact');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contacts = $this->contactService->getAll();
        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request)
    {
        $data = $request->validated();
        $contact = $this->contactService->create($data);
        return response_success('Contact message sent successfully', $contact, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        return response_success('Contact message details',$contact);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $this->contactService->delete($contact->id);
        return response_success('Contact message deleted successfully');
    }

    // Mark the specified contact message as read.
    public function markAsRead(Contact $contact)
    {
        $this->contactService->markAsRead($contact);
        return response_success('Contact message marked as read successfully.');
    }
}
