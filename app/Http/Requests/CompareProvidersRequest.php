<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class CompareProvidersRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'provider_ids' => 'required|array|min:2',
            'provider_ids.*' => 'required|integer|exists:insurance_providers,id',
        ];
    }
}

