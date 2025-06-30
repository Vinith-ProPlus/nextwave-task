<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;

abstract class FilterRequest extends Request
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
            'sort_by' => 'nullable|string|in:' . implode(',', $this->getSortableFields()),
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
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
            'sort_by.in' => 'The sort_by field must be one of: ' . implode(', ', $this->getSortableFields()),
            'sort_order.in' => 'The sort_order field must be either "asc" or "desc".',
            'per_page.min' => 'The per_page field must be at least 1.',
            'per_page.max' => 'The per_page field must not be greater than 100.',
            'end_date.after_or_equal' => 'The end_date must be after or equal to start_date.',
        ];
    }

    /**
     * Get sortable fields for the model
     *
     * @return array
     */
    abstract protected function getSortableFields(): array;

    /**
     * Get searchable fields for the model
     *
     * @return array
     */
    abstract protected function getSearchableFields(): array;

    /**
     * Get filterable fields configuration
     *
     * @return array
     */
    abstract protected function getFilterableFields(): array;
}
