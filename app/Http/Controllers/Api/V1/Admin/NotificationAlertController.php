<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NotificationAlertRequest;
use App\Http\Resources\Admin\NotificationAlerResource;
use App\Services\Admin\NotificationAlertService;
use Illuminate\Http\Request;
use Mockery\Matcher\Not;

class NotificationAlertController extends Controller
{
    protected NotificationAlertService $notificationAlertService;

    public function __construct(NotificationAlertService $notificationAlertService)
    {
        $this->notificationAlertService = $notificationAlertService;
    }


    // dashboard stats
    public function dashboardStats()
    {
        try {
            $totalMessages = $this->notificationAlertService->getTotalMessagesCount();
            $draftMessages = $this->notificationAlertService->getDraftMessagesCount();
            $scheduledMessages = $this->notificationAlertService->getScheduledMessagesCount();
            $sentMessages = $this->notificationAlertService->getSentMessagesCount();

            $data = [
                'total_messages' => $totalMessages,
                'draft_messages' => $draftMessages,
                'scheduled_messages' => $scheduledMessages,
                'sent_messages' => $sentMessages,
            ];
            return response_success('Message stats retrieved successfully.', $data);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => 'Failed to fetch message stats: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
           
            $notificationAlert = $this->notificationAlertService->getAll();
            if ($notificationAlert->isEmpty()) {
                return response_error('No notification alert found.', [], 404);
            }
            return NotificationAlerResource::collection($notificationAlert);
        } catch (\Exception $e) {
            return response_error('Failed to fetch notification alert: ' . $e->getMessage(), [], 500);
        }
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(NotificationAlertRequest $request)
    {
        try {
            $validatedData = $request->validated();
            //for this time action always send_now
            $validatedData['action'] = 'send_now';
            //** for future update if needed */
            // $status = 'draft';
            // if ($validatedData['action'] === 'schedule') {
            //     $status = 'scheduled';
            // }
            // $validatedData['status'] = $status;
            //**-------------***------------- */

            $validatedData['sender_id'] = auth()->id();
            $message = $this->notificationAlertService->create($validatedData);
            
            if ($message) {
                // If the action is to send immediately
                if ($validatedData['action'] == 'send_now') {
                    $this->notificationAlertService->sendMessage($message, $validatedData['recipient_type']);
                }

                return response_success('Message created successfully.', $message);
            }
            return response_error('Failed to create message.', [], 500);
        } catch (\Exception $e) {
            return response_error('Failed to create message: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $message = $this->notificationAlertService->getById((int)$id);
            if (!$message) {
                return response_error('Message not found.', [], 404);
            }
            return response_success('Message retrieved successfully.', $message);
        } catch (\Exception $e) {
            return response_error('Failed to retrieve message: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(MessageRequest $request, string $id)
    // {
    //     try {
    //         $validatedData = $request->validated();
    //         $message = $this->notificationAlertService->getById($id);
    //         // dd($validatedData)  ;
    //         if (!$message) {
    //             return response_error('Message not found.', [], 404);
    //         }

    //         if($message->status !== 'draft' && $message->status !== 'scheduled') {
    //             return response_error('Only draft or scheduled messages can be updated.', [], 403);
    //         }

    //         $status = 'draft';
    //         if ($validatedData['action'] === 'schedule') {
    //             $status = 'scheduled';
    //         }
    //         $validatedData['status'] = $status;
    //         $validatedData['sender_id'] = auth()->id();

    //         // Update the message
    //         $updatedMessage = $this->notificationAlertService->update($message->id, $validatedData);
    //         if ($updatedMessage) {
    //             return response_success('Message updated successfully.', $updatedMessage);
    //         }
    //         return response_error('Failed to update message.', [], 500);
    //     } catch (\Exception $e) {
    //         return response_error('Failed to update message: ' . $e->getMessage(), [], 500);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     try {
    //         $message = $this->notificationAlertService->getById($id);
    //         if (!$message) {
    //             return response_error('Message not found.', [], 404);
    //         }

    //         // Only allow deletion of draft or scheduled messages
    //         if ($message->status !== 'draft' && $message->status !== 'scheduled') {
    //             return response_error('Only draft or scheduled messages can be deleted.', [], 403);
    //         }

    //         $this->notificationAlertService->delete($id);
    //         return response_success('Message deleted successfully.', [], 200);
    //     } catch (\Exception $e) {
    //         return response_error('Failed to delete message: ' . $e->getMessage(), [], 500);
    //     }
    // }
}
