<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidationFactory;

class TaskFilterRequest extends Request
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
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'is_completed' => 'nullable|boolean',
            'is_overdue' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
            'assigned_by' => 'nullable|exists:users,id',
            'due_date_from' => 'nullable|date',
            'due_date_to' => 'nullable|date|after_or_equal:due_date_from',
            'created_at_from' => 'nullable|date',
            'created_at_to' => 'nullable|date|after_or_equal:created_at_from',
            'sort_by' => 'nullable|in:id,title,status,priority,due_date,created_at,updated_at',
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
            'status.in' => 'The status must be one of: pending, in_progress, completed, cancelled.',
            'priority.in' => 'The priority must be one of: low, medium, high, urgent.',
            'is_completed.boolean' => 'The is completed field must be true or false.',
            'is_overdue.boolean' => 'The is overdue field must be true or false.',
            'user_id.exists' => 'The selected user is invalid.',
            'assigned_by.exists' => 'The selected assigned by user is invalid.',
            'due_date_from.date' => 'The due date from must be a valid date.',
            'due_date_to.date' => 'The due date to must be a valid date.',
            'due_date_to.after_or_equal' => 'The due date to must be a date after or equal to due date from.',
            'created_at_from.date' => 'The created at from must be a valid date.',
            'created_at_to.date' => 'The created at to must be a valid date.',
            'created_at_to.after_or_equal' => 'The created at to must be a date after or equal to created at from.',
            'sort_by.in' => 'The sort by must be one of: id, title, status, priority, due_date, created_at, updated_at.',
            'sort_order.in' => 'The sort order must be one of: asc, desc.',
            'page.integer' => 'The page must be an integer.',
            'page.min' => 'The page must be at least 1.',
            'per_page.integer' => 'The per page must be an integer.',
            'per_page.min' => 'The per page must be at least 1.',
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
