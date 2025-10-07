<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class ReviewRequest extends BaseRequest
{

    // public function authorize(): bool
    // {
    //    return !$this->user()->hasRole('admin');
    // }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'provider_id' => ['required', 'exists:insurance_providers,id'],
            'state_id' => ['required', 'exists:states,id'],
            'overall_rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
            'pros' => ['nullable', 'array'],
            'pros.*' => ['string', 'max:255'],
            'cons' => ['nullable', 'array'],
            'cons.*' => ['string', 'max:255'],
            'scores' => ['required', 'array'],
            'scores.claims' => ['required', 'integer', 'min:1', 'max:5'],
            'scores.service' => ['required', 'integer', 'min:1', 'max:5'],
            'scores.pricing' => ['required', 'integer', 'min:1', 'max:5'],
            'scores.coverage' => ['required', 'integer', 'min:1', 'max:5'],
            'scores.trust' => ['required', 'integer', 'min:1', 'max:5'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            return array_map(function ($rule) {
                if (($key = array_search('required', $rule)) !== false) {
                    $rule[$key] = 'sometimes';
                }
                return $rule;
            }, $rules);
        }

        return $rules;

    }
}

