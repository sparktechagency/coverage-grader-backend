<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class ReportRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_name' => 'required|string|max:255',
            'type' => 'required|string|in:user,provider,review',
            'format' => 'required|string|in:pdf,csv',
            'start_date' => 'required|date',
            'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
        ];
    }
}

