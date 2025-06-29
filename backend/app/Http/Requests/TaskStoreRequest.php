<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidationFactory;

class TaskStoreRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date|after:today',
            'user_id' => 'nullable|exists:users,id',
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
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be one of: pending, in_progress, completed, cancelled.',
            'priority.required' => 'The priority field is required.',
            'priority.in' => 'The priority must be one of: low, medium, high, urgent.',
            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after' => 'The due date must be a date after today.',
            'user_id.exists' => 'The selected user is invalid.',
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
