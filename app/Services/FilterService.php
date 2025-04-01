<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

trait FilterService
{
    /**
     * Apply filters to the query based on filter map configuration
     */
    protected function applyFilters(Builder $query, array $filters, array $filterMap): Builder
    {
        foreach ($filterMap as $filterKey => $mapping) {
            if (!empty($filters[$filterKey])) {
                $this->applyFilter($query, $mapping, $filters[$filterKey]);
            }
        }
        return $query;
    }

    /**
     * Apply a single filter based on mapping configuration
     */
    protected function applyFilter(Builder $query, array|string $mapping, $value): void
    {
        if (is_string($mapping)) {
            $query->where($mapping, $value);
            return;
        }

        // Handle relation filters
        if (isset($mapping['relation'])) {
            $query->whereHas($mapping['relation'], fn($q) => 
                $this->applyWhereCondition($q, $mapping, $value)
            );
            return;
        }

        $this->applyWhereCondition($query, $mapping, $value);
    }

    /**
     * Apply the actual where condition
     */
    private function applyWhereCondition(Builder $query, array $mapping, $value): void
    {
        $field = $mapping['field'] ?? $mapping[0] ?? null;
        $operator = $mapping['operator'] ?? $mapping[1] ?? '=';

        match ($operator) {
            'like' => $query->where($field, 'like', "%{$value}%"),
            'in' => $query->whereIn($field, (array)$value),
            'search' => $this->applySearchFilter($query, $mapping, $value),
            default => $query->where($field, $operator, $value)
        };
    }

    /**
     * Apply search across multiple fields
     */
    private function applySearchFilter(Builder $query, array $mapping, $value): void
    {
        $query->where(function ($q) use ($mapping, $value) {
            foreach ($mapping['fields'] as $field) {
                $q->orWhere($field, 'like', "%{$value}%");
            }
        });
    }

    /**
     * Apply date range filters
     */
    protected function applyDateRange(Builder $query, ?string $dateFrom, ?string $dateTo, string $field = 'created_at'): void
    {
        if ($dateFrom && $this->isValidDate($dateFrom)) {
            $query->whereDate($field, '>=', $dateFrom);
        }

        if ($dateTo && $this->isValidDate($dateTo)) {
            $query->whereDate($field, '<=', $dateTo);
        }
    }

    /**
     * Validate date string format (YYYY-MM-DD)
     */
    protected function isValidDate(?string $date): bool
    {
        if (empty($date)) {
            return false;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}