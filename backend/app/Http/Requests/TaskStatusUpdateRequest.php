<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidationFactory;

class TaskStatusUpdateRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,in_progress,completed,cancelled',
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
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be one of: pending, in_progress, completed, cancelled.',
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
