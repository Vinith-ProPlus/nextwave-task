<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

trait Filterable
{
    /**
     * Apply filters, sorting, and pagination to a query
     *
     * @param Builder $query
     * @param Request $request
     * @param array $filters
     * @param array $searchableFields
     * @param array $sortableFields
     * @param string $defaultSortBy
     * @param string $defaultSortOrder
     * @param int $defaultPerPage
     * @return array
     */
    protected function applyFilters(
        Builder $query,
        Request $request,
        array $filters = [],
        array $searchableFields = [],
        array $sortableFields = [],
        string $defaultSortBy = 'created_at',
        string $defaultSortOrder = 'desc',
        int $defaultPerPage = 15
    ): array {
        // Validate request parameters
        $validator = $this->validateFilterRequest($request, $filters, $sortableFields);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
                'status' => 422
            ];
        }

        // Apply search
        if ($request->filled('search') && !empty($searchableFields)) {
            $this->applySearch($query, $request->search, $searchableFields);
        }

        // Treat start_date/end_date as aliases for created_at_from/created_at_to
        $dateFrom = $request->get('created_at_from') ?? $request->get('start_date');
        $dateTo = $request->get('created_at_to') ?? $request->get('end_date');
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Fields handled by custom filters for API logs
        $customFilterFields = ['status_code_range', 'min_duration', 'max_duration', 'created_at_from', 'created_at_to', 'start_date', 'end_date'];
        // Apply main filters
        foreach ($filters as $field => $config) {
            if (in_array($field, $customFilterFields)) continue;
            if ($request->filled($field)) {
                $this->applyFilter($query, $config['field'] ?? $field, $request->get($field), $config);
            }
        }
        // Apply custom filters (for API logs, tasks, etc.)
        $this->applyCustomFilters($query, $request);

        // Apply sorting
        $sortBy = $request->get('sort_by', $defaultSortBy);
        $sortOrder = $request->get('sort_order', $defaultSortOrder);
        $query->orderBy($sortBy, $sortOrder);

        // Apply pagination
        $perPage = $request->get('per_page', $defaultPerPage);
        $results = $query->paginate($perPage);

        // Build response
        $response = [
            'data' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
            ],
            'filters' => $this->getAppliedFilters($request, $filters, $sortBy, $sortOrder)
        ];

        return [
            'success' => true,
            'data' => $response
        ];
    }

    /**
     * Apply search functionality
     *
     * @param Builder $query
     * @param string $search
     * @param array $fields
     * @return void
     */
    protected function applySearch(Builder $query, string $search, array $fields): void
    {
        $query->where(function ($q) use ($search, $fields) {
            foreach ($fields as $field) {
                $q->orWhere($field, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Apply individual filter
     *
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     * @param array $config
     * @return void
     */
    protected function applyFilter(Builder $query, string $field, $value, array $config): void
    {
        $type = $config['type'] ?? 'exact';
        $operator = $config['operator'] ?? '=';

        switch ($type) {
            case 'exact':
                $query->where($field, $operator, $value);
                break;
            case 'boolean':
                $query->where($field, $operator, filter_var($value, FILTER_VALIDATE_BOOLEAN));
                break;
            case 'date_range':
                if (isset($config['start_field']) && isset($config['end_field'])) {
                    $this->applyDateRangeFilter($query, $field, $value, $config);
                } else {
                    $query->whereDate($field, $operator, $value);
                }
                break;
            case 'in':
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereIn($field, $values);
                break;
            case 'like':
                $query->where($field, 'like', "%{$value}%");
                break;
            case 'status_code_range':
                $this->applyStatusCodeRangeFilter($query, $field, $value);
                break;
        }
    }

    /**
     * Apply custom filters for specific use cases
     *
     * @param Builder $query
     * @param Request $request
     * @return void
     */
    protected function applyCustomFilters(Builder $query, Request $request): void
    {
        // Task-specific custom filters
        if ($request->filled('is_completed')) {
            $isCompleted = filter_var($request->is_completed, FILTER_VALIDATE_BOOLEAN);
            if ($isCompleted) {
                $query->whereNotNull('completed_at');
            } else {
                $query->whereNull('completed_at');
            }
        }

        if ($request->filled('is_overdue')) {
            $isOverdue = filter_var($request->is_overdue, FILTER_VALIDATE_BOOLEAN);
            if ($isOverdue) {
                $query->where('due_date', '<', Carbon::now())
                      ->whereNull('completed_at');
            } else {
                $query->where(function ($q) {
                    $q->where('due_date', '>=', Carbon::now())
                      ->orWhereNotNull('completed_at');
                });
            }
        }

        // API Log-specific custom filters
        if ($request->filled('status_code_range')) {
            $this->applyStatusCodeRangeFilter($query, 'status_code', $request->status_code_range);
        }

        if ($request->filled('min_duration') || $request->filled('max_duration')) {
            if ($request->filled('min_duration')) {
                $query->where('duration_ms', '>=', $request->min_duration);
            }
            if ($request->filled('max_duration')) {
                $query->where('duration_ms', '<=', $request->max_duration);
            }
        }
    }

    /**
     * Apply status code range filter
     *
     * @param Builder $query
     * @param string $field
     * @param string $range
     * @return void
     */
    protected function applyStatusCodeRangeFilter(Builder $query, string $field, string $range): void
    {
        switch ($range) {
            case '1xx':
                $query->whereBetween($field, [100, 199]);
                break;
            case '2xx':
                $query->whereBetween($field, [200, 299]);
                break;
            case '3xx':
                $query->whereBetween($field, [300, 399]);
                break;
            case '4xx':
                $query->whereBetween($field, [400, 499]);
                break;
            case '5xx':
                $query->whereBetween($field, [500, 599]);
                break;
        }
    }

    /**
     * Apply date range filter
     *
     * @param Builder $query
     * @param string $field
     * @param mixed $value
     * @param array $config
     * @return void
     */
    protected function applyDateRangeFilter(Builder $query, string $field, $value, array $config): void
    {
        if (isset($config['start_field']) && isset($config['end_field'])) {
            $startField = $config['start_field'];
            $endField = $config['end_field'];

            if (is_array($value)) {
                if (isset($value['start'])) {
                    $query->where($startField, '>=', $value['start'] . ' 00:00:00');
                }
                if (isset($value['end'])) {
                    $query->where($endField, '<=', $value['end'] . ' 23:59:59');
                }
            } else {
                // Single date value
                $query->where($startField, '>=', $value . ' 00:00:00');
                $query->where($endField, '<=', $value . ' 23:59:59');
            }
        }
    }

    /**
     * Validate filter request parameters
     *
     * @param Request $request
     * @param array $filters
     * @param array $sortableFields
     * @return \Illuminate\Validation\Validator
     */
    protected function validateFilterRequest(Request $request, array $filters, array $sortableFields): \Illuminate\Validation\Validator
    {
        $rules = [
            'search' => 'nullable|string|max:255',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];

        // Add sort_by validation
        if (!empty($sortableFields)) {
            $rules['sort_by'] = 'nullable|string|in:' . implode(',', $sortableFields);
        }

        // Add filter-specific validations
        foreach ($filters as $field => $config) {
            if (isset($config['validation'])) {
                $rules[$field] = $config['validation'];
            }
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Get applied filters for response
     *
     * @param Request $request
     * @param array $filters
     * @param string $sortBy
     * @param string $sortOrder
     * @return array
     */
    protected function getAppliedFilters(Request $request, array $filters, string $sortBy, string $sortOrder): array
    {
        $appliedFilters = [
            'search' => $request->search,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ];

        foreach (array_keys($filters) as $field) {
            $appliedFilters[$field] = $request->get($field);
        }

        return $appliedFilters;
    }
}
