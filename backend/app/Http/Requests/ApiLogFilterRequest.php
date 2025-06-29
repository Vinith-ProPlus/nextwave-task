<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidationFactory;

class ApiLogFilterRequest extends Request
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
            'method' => 'nullable|in:GET,POST,PUT,PATCH,DELETE',
            'status_code' => 'nullable|integer|min:100|max:599',
            'status_code_range' => 'nullable|string|regex:/^\d{3}-\d{3}$/',
            'user_id' => 'nullable|exists:users,id',
            'created_at_from' => 'nullable|date',
            'created_at_to' => 'nullable|date|after_or_equal:created_at_from',
            'duration_min' => 'nullable|numeric|min:0',
            'duration_max' => 'nullable|numeric|min:0|gte:duration_min',
            'sort_by' => 'nullable|in:id,method,status_code,user_id,created_at,duration',
            'sort_order' => 'nullable|in:asc,desc',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
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
            'method.in' => 'The method must be one of: GET, POST, PUT, PATCH, DELETE.',
            'status_code.integer' => 'The status code must be an integer.',
            'status_code.min' => 'The status code must be at least 100.',
            'status_code.max' => 'The status code may not be greater than 599.',
            'status_code_range.regex' => 'The status code range must be in format: 200-299.',
            'user_id.exists' => 'The selected user is invalid.',
            'created_at_from.date' => 'The created at from must be a valid date.',
            'created_at_to.date' => 'The created at to must be a valid date.',
            'created_at_to.after_or_equal' => 'The created at to must be a date after or equal to created at from.',
            'duration_min.numeric' => 'The duration min must be a number.',
            'duration_min.min' => 'The duration min must be at least 0.',
            'duration_max.numeric' => 'The duration max must be a number.',
            'duration_max.min' => 'The duration max must be at least 0.',
            'duration_max.gte' => 'The duration max must be greater than or equal to duration min.',
            'sort_by.in' => 'The sort by must be one of: id, method, status_code, user_id, created_at, duration.',
            'sort_order.in' => 'The sort order must be one of: asc, desc.',
            'page.integer' => 'The page must be an integer.',
            'page.min' => 'The page must be at least 1.',
            'per_page.integer' => 'The per page must be an integer.',
            'per_page.min' => 'The per page must be at least 1.',
            'per_page.max' => 'The per page may not be greater than 100.',
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
