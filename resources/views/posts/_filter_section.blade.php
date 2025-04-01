<div id="filterContent">
    <form id="filterForm" class="row g-3">
        <div class="col-md-3">
            <label for="search" class="form-label">Search</label>
            <input type="text" class="form-control" id="search" name="search" placeholder="Search...">
        </div>
        <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="filter_status" name="status">
                <option value="">All</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="author_id" class="form-label">Authors</label>
            <select class="form-select" id="filter_author_id" name="author_ids[]" multiple>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="tag_id" class="form-label">Tags</label>
            <select class="form-select" id="filter_tag_id" name="tag_ids[]" multiple>
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <div class="row">
                <div class="col-md-6">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from">
                </div>
                <div class="col-md-6">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to">
                </div>
            </div>
        </div>
        <div class="col-md-12 d-flex justify-content-end">
            <button type="button" class="btn btn-outline-danger" id="resetFilters">
                <i class="bi bi-arrow-counterclockwise"></i> Reset
            </button>
        </div>
    </form>
</div>