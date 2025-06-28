<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'priority' => 'sometimes|in:low,medium,high',
            'user_id' => 'required|exists:users,id',
            'due_date' => 'nullable|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'title.min' => 'The task title must be at least 3 characters.',
            'description.max' => 'The description cannot exceed 1000 characters.',
            'status.in' => 'The status must be pending, in_progress, completed, or cancelled.',
            'priority.in' => 'The priority must be low, medium, or high.',
            'user_id.required' => 'The user is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'due_date.date' => 'Please provide a valid date.',
            'due_date.after' => 'The due date must be in the future.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'timestamp' => now()->toISOString(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }
}
