<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidationFactory;

class UserFilterRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'role' => 'nullable|string|max:50',
            'created_at_from' => 'nullable|date',
            'created_at_to' => 'nullable|date|after_or_equal:created_at_from',
            'sort_by' => 'nullable|in:id,name,email,created_at,updated_at',
            'sort_order' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'search.max' => 'The search term may not be greater than 255 characters.',
            'is_active.boolean' => 'The is active field must be true or false.',
            'role.max' => 'The role may not be greater than 50 characters.',
            'created_at_from.date' => 'The created at from must be a valid date.',
            'created_at_to.date' => 'The created at to must be a valid date.',
            'created_at_to.after_or_equal' => 'The created at to must be a date after or equal to created at from.',
            'sort_by.in' => 'The sort by must be one of: id, name, email, created_at, updated_at.',
            'sort_order.in' => 'The sort order must be one of: asc, desc.',
            'page.integer' => 'The page must be an integer.',
            'page.min' => 'The page must be at least 1.',
            'per_page.integer' => 'The per page must be an integer.',
            'per_page.min' => 'The per page must be at least 1.'
        ];
    }

    /**
     * Validate the request.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $validator = app(ValidationFactory::class)->make($this->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        return true;
    }
}
