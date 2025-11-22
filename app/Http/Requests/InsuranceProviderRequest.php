<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class InsuranceProviderRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', //10 mb
            'about' => 'nullable|string',
            'pros'  => 'nullable|array',
            'pros.*'  => 'string|max:255',
            'cons'  => 'nullable|array',
            'cons.*'  => 'string|max:255',
            'price' => 'nullable|numeric|min:0',


            //states and policies will be handled separately
            'states' => 'nullable|array',
            'states.*' => 'exists:states,id',
            'policies' => 'nullable|array',
            'policies.*' => 'exists:policy_categories,id',
            'sponsored_url' => 'nullable|url',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $id = $this->route('id');
            $rules = [
                'name' => 'sometimes|string|max:255',
                'title' => 'sometimes|nullable|string|max:255',
                'logo_url' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
                'about' => 'sometimes|nullable|string',
                'pros'  => 'sometimes|nullable|array',
                'pros.*'  => 'string|max:255',
                'cons'  => 'sometimes|nullable|array',
                'cons.*'  => 'string|max:255',
                'price' => 'sometimes|nullable|numeric|min:0',
                'is_sponsored' => 'sometimes|boolean',
                'status' => 'sometimes|required|in:active,inactive',

                'states' => 'sometimes|nullable|array',
                'states.*' => 'exists:states,id',
                'policies' => 'sometimes|nullable|array',
                'policies.*' => 'exists:policy_categories,id',
                'sponsored_url' => 'sometimes|nullable|url',
            ];
        }

        return $rules;
    }
}
