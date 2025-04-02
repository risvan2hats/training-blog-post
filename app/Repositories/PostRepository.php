<?php

namespace App\Repositories;

use App\Models\Post;
use App\Traits\FilterService;
use Illuminate\Pagination\LengthAwarePaginator;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * PostRepository - Repository class for Post model
 * 
 * Handles all database operations for Post model including:
 * - CRUD operations
 * - Filtering and searching
 * - Relationship loading
 * - Data export preparation
 * 
 * @package App\Repositories
 */
class PostRepository extends BaseRepository
{
    use FilterService;

    /**
     * Mapping of filter parameters to their database operations
     * 
     * Structure:
     * [
     * ]
     * 
     * @var array
     */
    protected array $filterMap = [
        'title'     => 'ilike',
        'content'   => 'ilike',
        'status'    => '=',
        'author_id' => 'in',
        'date_from' => '>=',
        'date_to'   => '<=',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return Post::class;
    }

    /**
     * Boot the repository
     * 
     * Pushes criteria that should be applied to all queries
     */
    public function boot(): void
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Build the base query with relationships, filters, and ordering
     *
     * @param array $filters Associative array of filter parameters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    // protected function buildQuery(array $filters = []): \Illuminate\Database\Eloquent\Builder
    protected function buildQuery(array $filters = [])
    {
        dd("sdfs");
        // Initialize new query builder instance
        $query = $this->model->newQuery()
            // Eager load relationships
            ->with([
                'author', 
                'tags',
                'comments'
            ])
            // Add comments count
            ->withCount('comments');

        // Apply filters from FilterService trait
        $query = $this->applyFilters($query, $filters, $this->filterMap);

        // Apply ordering
        return $query->orderBy(
            $filters['order_by'] ?? 'id', // Default order field
            $filters['order_direction'] ?? 'desc' // Default order direction
        );
    }

    /**
     * Get paginated list of posts with filters
     *
     * @param array $filters Associative array of filter parameters including:
     *               - per_page: Items per page
     *               - order_by: Field to sort by
     *               - order_direction: Sort direction (asc/desc)
     *               - Other filter keys defined in $filterMap
     * @return LengthAwarePaginator
     */
    public function getPosts(array $filters = []): LengthAwarePaginator
    {
        return $this->buildQuery($filters)
            ->paginate($filters['per_page'] ?? 15); // Default to 15 items per page
    }

    public function getPostsNew()
    {
        return $this->model::query()->with('author', 'tags','comments');
    }

    public function filterWithdirectfields($query, $field, $value)
    {
        if (empty($value)) {
            return $query;
        }
        return is_array($value) ? $query->whereIn($field, $value) : $query->where($field, $value);
    }

    public function filterWithRelationDirectFields($query,$relation, $field, $value)
    {
        if (empty($value)) {
            return $query;
        }
        return $query->wherehas($relation(), fn($q) => is_array($value) ? $query->with($relation)->whereIn($field, $value) : $query->with($relation)->where($field, $value));
    }

    /**
     * Get query builder for posts export
     * 
     * Applies same filters as getPosts() but without pagination
     *
     * @param array $filters Associative array of filter parameters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getPostsForExport(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        return $this->buildQuery($filters);
    }
}