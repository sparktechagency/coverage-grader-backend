<?php

namespace App\Services\Admin;

use App\Services\BaseService;
use App\Models\NotificationAlert;
use App\Models\User;
use App\Notifications\Admin\CustomAlert;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\QueryBuilder\AllowedFilter;

class NotificationAlertService extends BaseService
{
    /**
     * The model class name.
     *
     * @var string
     */
    protected string $modelClass = NotificationAlert::class;

    public function __construct()
    {
        // Ensure BaseService initializes the model instance
        parent::__construct();
    }


    // Define allowed filters
     protected function getAllowedFilters(): array
    {
        return [
            'notification_type',
            AllowedFilter::exact('status'),
        ];
    }

    // Define allowed includes relationships
     protected function getAllowedIncludes(): array
     {
        return [
            'sender',
        ];
     }

     // Define allowed sorts
     protected function getAllowedSorts(): array
     {
        return [
            'id',
            'created_at',
        ];
     }

    /**
     * Sends a message to the specified recipients.
     */
    public function sendMessage(NotificationAlert $notificationAlert, string $recipientType): void
    {
        $query = User::query();

        switch ($recipientType) {
            case 'all':
                $recipients = $query->where('id', '!=', auth()->id())->get();
                Log::debug("Sending to all users except sender. Count: " . $recipients);
                break;
            case 'user':
                $recipients = $query->role('user')->get();
                Log::debug("Sending to user users except sender. Count: " . $recipients);
                break;
            case 'admin':
                $recipients = $query->where('id', '!=', auth()->id())->role('admin')->get();
                Log::debug("Sending to admin users except sender. Count: " . $recipients);
                break;
            default:
                throw new \InvalidArgumentException("Invalid recipient type: {$recipientType}");
        }
        Log::debug("Recipients count: " . $recipients->count());
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new CustomAlert($notificationAlert));
            $notificationAlert->update(['status' => 'sent']);
        }else{
            Log::warning("No recipients found for recipient type: {$recipientType}");
            $notificationAlert->update(['status' => 'draft']);
        }
        Log::info("NotificationAlert ID {$notificationAlert->id} sent to {$recipients->count()} recipients.");
    }

    // Get total messages count
    public function getTotalMessagesCount(): int
    {
        return $this->modelClass::count();
    }
    // Get draft messages count
    public function getDraftMessagesCount(): int
    {
        return $this->modelClass::where('status', 'draft')->count();
    }
    // Get scheduled messages count
    public function getScheduledMessagesCount(): int
    {
        return $this->modelClass::where('status', 'scheduled')->count();
    }
    // Get sent messages count
    public function getSentMessagesCount(): int
    {
        return $this->modelClass::where('status', 'sent')->count();
    }
}
