<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PoliceCategoryRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $policyCategory = $this->route('policy');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('policy_categories', 'name')
                    ->ignore($policyCategory), 
            ],
            'description' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:5120'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
