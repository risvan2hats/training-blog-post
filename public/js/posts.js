$(document).ready(function () {
    // Base API URL
    const API_BASE_URL = '/api';

    // DOM Elements
    const $postsList = $('#posts-list');
    const $commentsModal = $('#commentsModal');
    const $postFormModal = $('#postFormModal');
    const $filterSection = $('#filterSection');
    const $filterAuthor = $('#filter_author_id');
    const $filterTag = $('#filter_tag_id');

    // Current state
    let currentFilters = {};
    let currentPage = 1;

    // Initialize the application
    init();

    function init() {
        initializeSelect2();
        loadPosts();
        setupEventListeners();
        loadSavedFilters();
    }

    function initializeSelect2() {
        $filterAuthor.select2({
            placeholder: "Select authors",
            allowClear: true,
            width: '100%'
        });

        $filterTag.select2({
            placeholder: "Select tags",
            allowClear: true,
            width: '100%'
        });
    }

    function handlePagination(e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadPosts(currentFilters, page);
    }
    
    function setupEventListeners() {
        // Toggle filter section
        $('#toggleFilterBtn').click(function() {
            $filterSection.slideToggle();
            $(this).toggleClass('btn-outline-secondary btn-secondary');
        });

        // Filter related
        $('#resetFilters').click(resetFilters);
        $('#search').on('input', debounce(applyFilters, 500));
        $('#filter_status').change(applyFilters);
        $filterAuthor.on('change', applyFilters);
        $filterTag.on('change', applyFilters);
        $('#date_from, #date_to').change(applyFilters);

        // Post CRUD
        $('#addPostBtn').click(showAddPostModal);
        $('#savePostBtn').click(savePost);
        $(document).on('click', '.edit-post', showEditPostModal);
        $(document).on('click', '.delete-post', deletePost);

        // Comments
        $(document).on('click', '.comment-count', showCommentsModal);
        $('#commentForm').submit(addComment);
        $(document).on('click', '.delete-comment', deleteComment);

        // Image handling
        $('#image').change(previewImage);
        $(document).on('click', '.remove-image-btn', handleImageRemoval);

        // Pagination
        $(document).on('click', '.page-link', handlePagination);

        // Save filters when inputs change
        $('#filterForm').on('change input', saveFilters);

        // Export
        $('#exportBtn').click(exportToExcel);

        // Modal events
        $postFormModal.on('hidden.bs.modal', resetPostForm);
    }

    // Load saved filters from localStorage
    function loadSavedFilters() {
        const savedFilters = localStorage.getItem('postFilters');
        if (savedFilters) {
            const filters = JSON.parse(savedFilters);
            currentFilters = filters;

            // Set form values
            $('#search').val(filters.search || '');
            $('#filter_status').val(filters.status || '');
            
            // Set multi-select values
            if (filters.author_ids && filters.author_ids.length > 0) {
                $filterAuthor.val(filters.author_ids).trigger('change');
            }
            
            if (filters.tag_ids && filters.tag_ids.length > 0) {
                $filterTag.val(filters.tag_ids).trigger('change');
            }
            
            $('#date_from').val(filters.date_from || '');
            $('#date_to').val(filters.date_to || '');
        }
    }

    // Save filters to localStorage
    function saveFilters() {
        const filters = {
            search: $('#search').val(),
            status: $('#filter_status').val(),
            author_ids: $filterAuthor.val() || [],
            tag_ids: $filterTag.val() || [],
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val()
        };
        localStorage.setItem('postFilters', JSON.stringify(filters));
    }

    // Debounce function to limit rapid requests
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // Load posts with optional filters
    function loadPosts(filters = {}, page = 1) {
        // Convert filters to URLSearchParams to handle arrays properly
        const params = new URLSearchParams();
        params.append('page', page);
        
        // Add filters to params
        Object.entries(filters).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                value.forEach(v => params.append(`${key}[]`, v));
            } else if (value) {
                params.append(key, value);
            }
        });

        showLoading(true);

        $.ajax({
            url: `${API_BASE_URL}/posts?${params.toString()}`,
            type: 'GET',
            success: function(response) {
                if (response.data) {
                    renderPosts(response.data.data, response.data.meta);
                    renderPagination(response.data.meta);
                }
                currentFilters = filters;
                currentPage = page;
            },
            error: handleAjaxError,
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Render posts to the table
    function renderPosts(posts, meta) {
        let html = `
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
                <tbody>`;

        if (posts.length > 0) {
            posts.forEach((post, index) => {
                let serialNumber = meta.from + index;
                html += `
                    <tr>
                        <td>${serialNumber}</td>
                        <td>${post.title}</td>
                        <td>${post.author}</td>
                        <td>
                            <span class="badge bg-${post.status === 'active' ? 'success' : 'secondary'}">
                                ${post.status.charAt(0).toUpperCase() + post.status.slice(1)}
                            </span>
                        </td>
                        <td>${post.published_at || 'N/A'}</td>
                        <td>
                            <span class="comment-count" data-post-id="${post.id}">
                                ${post.comments_count}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-post" data-id="${post.id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-post" data-id="${post.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });
        } else {
            html += `<tr><td colspan="7" class="text-center">No posts found</td></tr>`;
        }

        html += `</tbody></table>`;
        $postsList.html(html);
    }

    // Render pagination controls
    function renderPagination(meta) {
        let html = `
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">`;

        // Previous button
        if (meta.current_page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" data-page="${meta.current_page - 1}">Previous</a>
            </li>`;
        }

        // Page numbers
        for (let i = 1; i <= meta.last_page; i++) {
            html += `<li class="page-item ${i === meta.current_page ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }

        // Next button
        if (meta.current_page < meta.last_page) {
            html += `<li class="page-item">
                <a class="page-link" href="#" data-page="${meta.current_page + 1}">Next</a>
            </li>`;
        }

        html += `</ul></nav>`;
        $('.pagination-container').html(html);
    }

    // Apply filters automatically on change
    function applyFilters() {
        const filters = {
            search: $('#search').val(),
            status: $('#filter_status').val(),
            author_ids: $filterAuthor.val() || [],
            tag_ids: $filterTag.val() || [],
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val()
        };

        // Validate date range
        if (filters.date_from && filters.date_to && filters.date_from > filters.date_to) {
            showAlert('danger', 'End date must be after start date');
            return;
        }

        loadPosts(filters);
    }

    // Reset all filters
    function resetFilters() {
        $('#filterForm')[0].reset();
        $filterAuthor.val(null).trigger('change');
        $filterTag.val(null).trigger('change');
        currentFilters = {};
        localStorage.removeItem('postFilters');
        loadPosts({});
    }

    // Show add post modal
    function showAddPostModal() {
        resetPostForm();
        $('#postFormModalLabel').text('Add Post');
        $postFormModal.modal('show');
        
        // Set default published date
        const now = new Date();
        const formattedDate = now.toISOString().slice(0, 16);
        $('#published_at').val(formattedDate);
    }

    // Reset post form
    function resetPostForm() {
        $('#postForm')[0].reset();
        $('#post_id').val('');
        $('#image').val('');
        $('#imagePreviewContainer').empty();
        $('#tags').val([]).trigger('change');
        $('.is-invalid').removeClass('is-invalid');
        $('.error-message').remove();
    }

    // Show edit post modal
    function showEditPostModal() {
        const postId = $(this).data('id');
        showLoading(true);
        resetPostForm();
        
        $.ajax({
            url: `${API_BASE_URL}/posts/${postId}`,
            type: 'GET',
            success: function(response) {
                const post = response.data;

                $('#post_id').val(post.id);
                $('#title').val(post.title);
                $('#content').val(post.content);
                $('#author_id').val(post.author_id);
                $('#status').val(post.status);
                
                if (post.published_at) {
                    $('#published_at').val(post.published_at.slice(0, 16));
                }

                if (post.tags?.length) {
                    $('#tags').val(post.tags.map(tag => tag.id)).trigger('change');
                }

                if (post.image_url) {
                    showCurrentImage(post.image_url);
                }

                $('#postFormModalLabel').text('Edit Post');
                $postFormModal.modal('show');
            },
            error: handleAjaxError,
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Save post (create or update)
    function savePost() {
        const formData = new FormData($('#postForm')[0]);
        const postId = $('#post_id').val();
        const url = postId ? `${API_BASE_URL}/posts/${postId}` : `${API_BASE_URL}/posts`;
        const method = 'POST';

        if (postId) {
            formData.append('_method', 'PUT');
        }

        // Only include image if selected
        if ($('#image')[0].files.length === 0) {
            formData.delete('image');
        }

        // Add tags
        const selectedTags = $('#tags').val() || [];
        selectedTags.forEach(tag => formData.append('tags[]', tag));

        showLoading(true);
        clearValidationErrors();

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $postFormModal.modal('hide');
                loadPosts(currentFilters, currentPage);
                showAlert('success', response.message);
            },
            error: function(xhr) {
                showLoading(false);
                if (xhr.status === 422) {
                    handleValidationErrors(xhr.responseJSON.errors);
                } else {
                    showAlert('danger', 'Something went wrong. Please try again.');
                }
            },
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Clear validation errors
    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.error-message').remove();
    }

    // Handle validation errors
    function handleValidationErrors(errors) {
        Object.keys(errors).forEach(key => {
            const field = $(`#${key}`);
            if (field.length) {
                field.addClass('is-invalid');
                field.after(`<div class="text-danger error-message">${errors[key][0]}</div>`);
            }
        });
    }

    // Delete post
    function deletePost() {
        if (!confirm('Are you sure you want to delete this post?')) return;

        const postId = $(this).data('id');
        showLoading(true);

        $.ajax({
            url: `${API_BASE_URL}/posts/${postId}`,
            type: 'DELETE',
            success: function(response) {
                loadPosts(currentFilters, currentPage);
                showAlert('success', response.message);
            },
            error: handleAjaxError,
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Show comments modal for a post
    function showCommentsModal() {
        const postId = $(this).data('post-id');
        $('#comment_post_id').val(postId);
        showLoading(true);

        $.ajax({
            url: `${API_BASE_URL}/posts/${postId}/comments`,
            type: 'GET',
            success: function(response) {
                renderComments(response.data.data);
                $commentsModal.modal('show');
            },
            error: handleAjaxError,
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Render comments in the modal
    function renderComments(comments) {
        let html = '';
        if (comments.length > 0) {
            comments.forEach(comment => {
                const canDelete = comment.created_user_id === window.authUser?.id;
                const deleteButton = canDelete 
                    ? `<button class="btn btn-sm btn-danger delete-comment" data-id="${comment.id}">
                          <i class="bi bi-trash"></i>
                       </button>`
                    : `<button class="btn btn-sm btn-danger" disabled 
                          title="You can only delete your own comments" 
                          data-bs-toggle="tooltip">
                          <i class="bi bi-trash"></i>
                       </button>`;
    
                html += `
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2">${comment.created_user_name}</span>
                                    <small class="text-muted">${formatDate(comment.created_at)}</small>
                                </div>
                                ${deleteButton}
                            </div>
                            <p class="card-text mb-0">${comment.content}</p>
                        </div>
                    </div>`;
            });
        } else {
            html = '<p class="text-center text-muted py-3">No comments yet. Be the first to comment!</p>';
        }
    
        $('#commentsList').html(html);
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    // Format date
    function formatDate(dateString) {
        return new Date(dateString).toLocaleString();
    }

    // Add new comment
    function addComment(e) {
        e.preventDefault();
        showLoading(true);

        $.ajax({
            url: `${API_BASE_URL}/comments`,
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#comment_content').val('');
                const postId = $('#comment_post_id').val();
                loadComments(postId);
                loadPosts(currentFilters, currentPage);
                showAlert('success', response.message);
            },
            error: handleAjaxError,
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Load comments for a post
    function loadComments(postId) {
        $.ajax({
            url: `${API_BASE_URL}/posts/${postId}/comments`,
            type: 'GET',
            success: function(response) {
                renderComments(response.data.data);
            },
            error: handleAjaxError
        });
    }

    // Delete comment
    function deleteComment() {
        if (!confirm('Are you sure you want to delete this comment?')) return;

        const commentId = $(this).data('id');
        showLoading(true);

        $.ajax({
            url: `${API_BASE_URL}/comments/${commentId}`,
            type: 'DELETE',
            success: function(response) {
                const postId = $('#comment_post_id').val();
                loadComments(postId);
                loadPosts(currentFilters, currentPage);
                showAlert('success', response.message);
            },
            error: handleAjaxError,
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Preview image before upload
    function previewImage() {
        const file = this.files[0];
        const container = $('#imagePreviewContainer');
        container.empty();
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                container.html(`
                    <div class="image-preview-wrapper">
                        <img src="${e.target.result}" class="img-thumbnail" width="200">
                        <button type="button" class="remove-image-btn" title="Remove image">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                `);
            };
            reader.readAsDataURL(file);
        }
    }

    // Handle current image display for edits
    function showCurrentImage(imageUrl) {
        $('#imagePreviewContainer').html(`
            <div class="image-preview-wrapper">
                <img src="${imageUrl}" class="img-thumbnail" width="200">
                <button type="button" class="remove-image-btn" title="Remove image">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `);
    }

    // Handle image removal
    function handleImageRemoval() {
        const postId = $('#post_id').val();
        const container = $('#imagePreviewContainer');
        
        if (postId) {
            if (confirm('Are you sure you want to remove this image?')) {
                showLoading(true);
                $.ajax({
                    url: `${API_BASE_URL}/posts/${postId}/image`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        container.empty();
                        $('#image').val('');
                        showAlert('success', 'Image removed successfully');
                    },
                    error: handleAjaxError,
                    complete: function() {
                        showLoading(false);
                    }
                });
            }
        } else {
            container.empty();
            $('#image').val('');
        }
    }

    // Show loading indicator
    function showLoading(show) {
        if (show) {
            $('body').append(`
                <div class="loading-overlay">
                    <div class="spinner-wrapper">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            `);
            $('body').addClass('blur-background');
        } else {
            $('.loading-overlay').remove();
            $('body').removeClass('blur-background');
        }
    }

    // Show alert message
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('body').append(alertHtml);
        setTimeout(() => $('.alert').alert('close'), 3000);
    }

    // Handle AJAX errors
    function handleAjaxError(xhr) {
        let errorMessage = 'An error occurred';
        if (xhr.responseJSON?.message) {
            errorMessage = xhr.responseJSON.message;
        } else if (xhr.statusText) {
            errorMessage = xhr.statusText;
        }
        showAlert('danger', errorMessage);
    }

    // Export to Excel
    function exportToExcel() {
        const filters = {
            search: $('#search').val(),
            status: $('#filter_status').val(),
            author_ids: $filterAuthor.val() || [],
            tag_ids: $filterTag.val() || [],
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val()
        };

        // Validate date range
        if (filters.date_from && filters.date_to && filters.date_from > filters.date_to) {
            showAlert('danger', 'End date must be after start date');
            return;
        }

        showLoading(true);

        // Convert filters to query string
        const params = new URLSearchParams();
        Object.entries(filters).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                value.forEach(v => params.append(`${key}[]`, v));
            } else if (value) {
                params.append(key, value);
            }
        });

        $.ajax({
            url: `${API_BASE_URL}/posts/export/excel?${params.toString()}`,
            type: 'GET',
            xhrFields: { responseType: 'blob' },
            success: function(data, status, xhr) {
                const filename = getFilenameFromHeaders(xhr) || 'posts.xlsx';
                const blob = new Blob([data], { type: xhr.getResponseHeader('content-type') });
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.URL.revokeObjectURL(link.href);
                showAlert('success', 'Export completed successfully');
            },
            error: handleAjaxError,
            complete: function() {
                showLoading(false);
            }
        });
    }

    // Helper function to extract filename from headers
    function getFilenameFromHeaders(xhr) {
        const contentDisposition = xhr.getResponseHeader('content-disposition');
        if (!contentDisposition) return null;
        const filenameMatch = contentDisposition.match(/filename="?(.+)"?/);
        return filenameMatch?.[1];
    }
});