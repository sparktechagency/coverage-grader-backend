<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class BlogRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'category_id' => ['required', 'exists:policy_categories,id'],
            'title' => [
                'required',
                'string',
                'max:255',
                // Unique rule shoriye fela hoyeche
            ],
            'author_name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'featured_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:10240'], // max 10MB
            'status' => ['nullable', 'string', Rule::in(['draft', 'published'])],
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

