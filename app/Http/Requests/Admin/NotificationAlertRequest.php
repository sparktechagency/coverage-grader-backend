<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class NotificationAlertRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => 'required|string',
            'notification_type' => 'required|string',
            'recipient_type' => 'required|string|in:all,admin,user',
            'action' => 'nullable|string|in:send_now,save_draft,schedule',
            'scheduled_at' => 'required_if:action,schedule|nullable|date|after:now',
        ];
    }
}

