<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Builder;

trait FilterService
{
    public $perPage = 15;
    public $orderBy = 'id';
    public $sortBy  = 'desc';
    
    /**
     * Get base query builder instance
     */
    protected function baseQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Get all records with pagination, filters, search and ordering
     */
    public function getAllFiltered($paginate = true)
    {
        $query = $this->baseQuery();
        
        // Eager load relationships if specified
        if (!empty($this->params['with'])) {
            $query->with(Arr::wrap($this->params['with']));
        }
        
        $this->applySearchFilter($query);
        $this->applyFilterCondition($query);
        $this->applyOrdering($query);
        
        if($paginate) {
            return $query->paginate(request()->input('per_page', $this->perPage));
        } else {
            return $query->get();
        }
    }

    /**
     * Apply ordering to the query
     */
    protected function applyOrdering(Builder $query): void
    {
        $orderBy    = request()->input('order_by', $this->orderBy);
        $sortBy     = strtolower(request()->input('sort_by', $this->sortBy));
        
        // Validate sort direction
        $sortBy     = in_array($sortBy, ['asc', 'desc']) ? $sortBy : 'desc';
        
        // Handle nested ordering (relationship columns)
        if (str_contains($orderBy, '.')) {
            $this->applyNestedOrdering($query, $orderBy, $sortBy);
        } else {
            $query->orderBy($orderBy, $sortBy);
        }
    }

    /**
     * Apply ordering for nested relations
     */
    protected function applyNestedOrdering(Builder $query, string $field, string $direction): void
    {
        [$relation, $column] = explode('.', $field);
        
        $query->with([$relation => function ($q) use ($column, $direction) {
            $q->orderBy($column, $direction);
        }]);
    }

    /**
     * Apply search filter if search parameter exists
     */
    protected function applySearchFilter(Builder $query): void
    {
        if (!request()->has('search') && empty(request('search')) && empty($this->searchColumns)) {
            return;
        }

        $searchTerm = request('search');
        
        // Only proceed if we have a non-empty search term
        if (empty($searchTerm)) {
            return;
        }

        $query->where(function ($q) use ($searchTerm) {
            foreach ($this->searchColumns as $field) {
                $this->applySearchCondition($q, $field, $searchTerm);
            }
        });
    }

    /**
     * Apply search condition for a single field with integrated field configuration
     */
    protected function applySearchCondition(Builder $query, string $field, string $searchTerm): void
    {
        // Get field configuration from filterMap
        $fieldConfig = [];
        foreach ($this->filterMap as $key => $config) {
            if (is_array($config)) {
                $configField = $config['key'] ?? $config['field'] ?? $key;
                if ($configField === $field) {
                    $fieldConfig = $config;
                    break;
                }
            }
        }

        // Set up search parameters
        $fieldName      = $fieldConfig['key'] ?? $fieldConfig['field'] ?? $field;
        $condition      = $fieldConfig['condition'] ?? 'like';
        $searchValue    = '%' . $searchTerm . '%';
        $relation       = $fieldConfig['whereHas'] ?? null;

        // Apply the appropriate condition based on field type
        if (str_contains($fieldName, '.')) {
            $this->applyNestedFilter($query,$fieldName,$condition,$searchValue,$relation,true // isOrWhere for search conditions
            );
        } else {
            $query->orWhere($fieldName, $condition, $searchValue);
        }
    }

    /**
     * Apply all field filters to the query
     */
    protected function applyFilterCondition(Builder $query): void
    {
        $requestParams = request()->except(['search', 'order_by', 'sort_by']);
        
        foreach ($requestParams as $field => $value) {
            // Skip empty values and undefined filters
            if (empty($value) || !isset($this->filterMap[$field])) {
                continue;
            }
            
            $mapping    = $this->filterMap[$field];
            $condition  = $mapping['condition'] ?? '=';
            
            // Handle array values differently
            if (is_array($value) || $condition === 'in') {
                $values = is_array($value) ? $value : [$value];
                $this->applyArrayFilter($query, $field, $values);
            } else {
                $this->applyFieldFilter($query, $field, $value);
            }
        }
    }

    /**
     * Special handler for array filters (IN conditions)
     */
    protected function applyArrayFilter(Builder $query, string $field, array $values): void
    {
        $mapping    = $this->filterMap[$field] ?? [];
        $fieldName  = $mapping['key'] ?? $mapping['field'] ?? $field;
        $type       = $mapping['type'] ?? 'string';
        
        // Process each value according to its type
        $processedValues = array_map(function($value) use ($type) {
            return match ($type) {
                'int'   => (int)$value,
                'float' => (float)$value,
                'bool'  => (bool)$value,
                'date'  => $this->parseDate($value),
                default => (string)$value
            };
        }, $values);

        // Handle nested relations
        if (str_contains($fieldName, '.')) {
            $this->applyNestedFilter($query, $fieldName, 'in', $processedValues, $mapping['whereHas'] ?? null);
        } else {
            $query->whereIn($fieldName, $processedValues);
        }
    }

    /**
     * Apply filter for a single field value (non-array)
     */
    protected function applyFieldFilter(Builder $query, string $field, $value): void
    {
        $mapping = $this->filterMap[$field] ?? [];
        
        if (empty($mapping)) {
            return;
        }

        $fieldName  = $mapping['key'] ?? $mapping['field'] ?? $field;
        $type       = $mapping['type'] ?? 'string';
        $condition  = $mapping['condition'] ?? '=';
        
        $processedValue = match ($type) {
            'int'   => (int)$value,
            'float' => (float)$value,
            'bool'  => (bool)$value,
            'date'  => $this->parseDate($value),
            default => (string)$value
        };

        if (str_contains($fieldName, '.')) {
            $this->applyNestedFilter($query, $fieldName, $condition, $processedValue, $mapping['whereHas'] ?? null);
        } else {
            $this->applyCondition($query, $fieldName, $condition, $processedValue, $type);
        }
    }

     /**
     * Apply filter on nested relations
     */
    protected function applyNestedFilter(Builder $query, string $field, string $condition, $value, ?string $relation = null, bool $isOrWhere = false): void
    {
        [$relationName, $column] = explode('.', $field);

        $relation   = $relation ?? $relationName;
        $method     = $isOrWhere ? 'orWhereHas' : 'whereHas';

        $query->$method($relation, function ($q) use ($column, $condition, $value) {

            $table              = $q->getModel()->getTable();
            $qualifiedColumn    = "{$table}.{$column}";

            $this->applyCondition($q, $qualifiedColumn, $condition, $value);
        });
    }

    /**
     * Apply final where condition
     */
    protected function applyCondition(Builder $query, string $field, string $condition, $value, string $type = 'string'): void
    {
        if ($type === 'date') {
            $this->applyDateCondition($query, $field, $condition, $value);
            return;
        }

        match ($condition) {
            'in'        => $query->whereIn($field, Arr::wrap($value)),
            'not_in'    => $query->whereNotIn($field, Arr::wrap($value)),
            'like'      => $query->where($field, $condition, $value),
            default     => $query->where($field, $condition, $value)
        };
    }

    /**
     * Apply date-specific conditions
     */
    protected function applyDateCondition(Builder $query, string $field, string $condition, $value): void
    {
        match ($condition) {
            '>='    => $query->whereDate($field, '>=', $value),
            '<='    => $query->whereDate($field, '<=', $value),
            default => $query->whereDate($field, $condition, $value)
        };
    }

    /**
     * Parse date value to consistent format
     */
    protected function parseDate($value): string
    {
        return date('Y-m-d', is_numeric($value) ? $value : strtotime($value));
    }
}