@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Posts</h5>
            <div>
                <button class="btn btn-outline-secondary btn-sm filter-btn" id="toggleFilterBtn">
                    <i class="bi bi-funnel-fill"></i> Filter
                </button>
                <button class="btn btn-primary btn-sm ms-2" id="addPostBtn">
                    <i class="bi bi-plus"></i> Add Post
                </button>
                <button class="btn btn-success btn-sm ms-2" id="exportBtn">
                    <i class="bi bi-file-excel"></i> Export
                </button>
            </div>
        </div>
        
        <!-- Filter Section (initially hidden) -->
        <div class="card-body border-bottom" id="filterSection" style="display: none;">
            @include('posts._filter_section')
        </div>
        
        <div class="card-body">
            <div id="posts-list">
                @include('posts._list')
            </div>
            <div class="d-flex justify-content-center pagination-container"></div>
        </div>
    </div>
@endsection

@section('modals')
    @include('posts._comments_modal')
    @include('posts._form')
@endsection

@section('scripts')
    <script src="{{ asset('js/posts.js') }}"></script>
@endsection