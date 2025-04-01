@isset($posts)
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>SL No</th>
            <th>Title</th>
            <th>Author</th>
            <th>Status</th>
            <th>Published Date</th>
            <th>Comments</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($posts as $index => $post)
            <tr>
                <td>{{ ($posts->currentPage() - 1) * $posts->perPage() + $index + 1 }}</td>
                <td>{{ $post->title }}</td>
                <td>{{ $post->author->name }}</td>
                <td>
                    <span class="badge bg-{{ $post->status === 'active' ? 'success' : 'secondary' }}">
                        {{ ucfirst($post->status) }}
                    </span>
                </td>
                <td>
                    @if($post->published_at)
                        {{ \Carbon\Carbon::parse($post->published_at)->format('Y-m-d') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    <span class="comment-count" data-post-id="{{ $post->id }}">
                        {{ $post->comments_count }}
                    </span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary edit-post" data-id="{{ $post->id }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-post" data-id="{{ $post->id }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No posts found</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($posts instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="d-flex justify-content-center pagination-container">
        {{ $posts->links() }}
    </div>
@endif
@endisset
