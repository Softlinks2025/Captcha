@extends('admin.layouts.app')

@section('title', 'Banner Management')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Banner Management</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addBannerModal">
            <i class="fas fa-plus"></i> Add New Banner
        </button>
    </div>

    <!-- Content Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Banners</h6>
            <div class="d-flex">
                <select id="statusFilter" class="form-control form-control-sm mr-2">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <select id="platformFilter" class="form-control form-control-sm mr-2">
                    <option value="">All Platforms</option>
                    <option value="all">All</option>
                    <option value="android">Android</option>
                    <option value="ios">iOS</option>
                    <option value="web">Web</option>
                </select>
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="bannersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Platform</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
                <div id="pagination" class="mt-3">
                    <!-- Pagination will be inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Banner Modal -->
<div class="modal fade" id="addBannerModal" tabindex="-1" role="dialog" aria-labelledby="addBannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBannerModalLabel">Add New Banner</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addBannerForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="image">Banner Image <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="image" name="image" accept="image/*" required>
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Recommended size: 1200x400px, Max size: 2MB</small>
                        <div id="imagePreview" class="mt-2 text-center" style="display: none;">
                            <img id="previewImage" src="#" alt="Preview" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="url">Target URL</label>
                        <input type="url" class="form-control" id="url" name="url" placeholder="https://example.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="target_platform">Target Platform <span class="text-danger">*</span></label>
                        <select class="form-control" id="target_platform" name="target_platform" required>
                            <option value="all">All Platforms</option>
                            <option value="android">Android</option>
                            <option value="ios">iOS</option>
                            <option value="web">Web</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" checked>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Banner Modal -->
<div class="modal fade" id="editBannerModal" tabindex="-1" role="dialog" aria-labelledby="editBannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBannerModalLabel">Edit Banner</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editBannerForm" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_banner_id" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_title">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_image">Banner Image</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="edit_image" name="image" accept="image/*">
                            <label class="custom-file-label" for="edit_image">Choose new file (leave empty to keep current)</label>
                        </div>
                        <small class="form-text text-muted">Recommended size: 1200x400px, Max size: 2MB</small>
                        <div id="editImagePreview" class="mt-2 text-center">
                            <img id="editPreviewImage" src="#" alt="Current Image" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_url">Target URL</label>
                        <input type="url" class="form-control" id="edit_url" name="url" placeholder="https://example.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_start_date">Start Date</label>
                                <input type="datetime-local" class="form-control" id="edit_start_date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_end_date">End Date</label>
                                <input type="datetime-local" class="form-control" id="edit_end_date" name="end_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_target_platform">Target Platform <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_target_platform" name="target_platform" required>
                            <option value="all">All Platforms</option>
                            <option value="android">Android</option>
                            <option value="ios">iOS</option>
                            <option value="web">Web</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_is_active" name="is_active">
                            <label class="custom-control-label" for="edit_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Banner Modal -->
<div class="modal fade" id="viewBannerModal" tabindex="-1" role="dialog" aria-labelledby="viewBannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewBannerModalLabel">Banner Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-4">
                    <div class="col-md-12 text-center">
                        <img id="view_image" src="#" alt="Banner Image" class="img-fluid rounded" style="max-height: 300px;">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Title:</label>
                            <p id="view_title" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Status:</label>
                            <p id="view_status" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="font-weight-bold">Description:</label>
                            <p id="view_description" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Target URL:</label>
                            <p id="view_url" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Target Platform:</label>
                            <p id="view_platform" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Start Date:</label>
                            <p id="view_start_date" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">End Date:</label>
                            <p id="view_end_date" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Created At:</label>
                            <p id="view_created_at" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Updated At:</label>
                            <p id="view_updated_at" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteBannerModal" tabindex="-1" role="dialog" aria-labelledby="deleteBannerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBannerModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this banner? This action cannot be undone.
                <input type="hidden" id="delete_banner_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Image preview for add form
        $('#image').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewImage').attr('src', e.target.result);
                    $('#imagePreview').show();
                }
                reader.readAsDataURL(file);
                $('.custom-file-label').text(file.name);
            }
        });

        // Image preview for edit form
        $('#edit_image').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#editPreviewImage').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
                $('.custom-file-label').text(file.name);
            }
        });

        // Load banners on page load
        loadBanners();

        // Search functionality
        $('#searchInput').on('keyup', function() {
            loadBanners();
        });

        // Filter functionality
        $('#statusFilter, #platformFilter').on('change', function() {
            loadBanners();
        });

        // Add new banner
        $('#addBannerForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            $.ajax({
                url: '/admin/banners',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#addBannerModal').modal('hide');
                        $('#addBannerForm')[0].reset();
                        $('#imagePreview').hide();
                        showAlert('success', 'Banner added successfully!');
                        loadBanners();
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'An error occurred');
                }
            });
        });

        // Edit banner - open modal
        $(document).on('click', '.edit-btn', function() {
            const bannerId = $(this).data('id');
            
            $.get(`/admin/banners/${bannerId}`, function(response) {
                if (response.success) {
                    const banner = response.data;
                    $('#edit_banner_id').val(banner.id);
                    $('#edit_title').val(banner.title);
                    $('#edit_description').val(banner.description);
                    $('#edit_url').val(banner.url || '');
                    $('#edit_is_active').prop('checked', banner.is_active);
                    $('#edit_target_platform').val(banner.target_platform);
                    
                    // Format dates for datetime-local input
                    if (banner.start_date) {
                        $('#edit_start_date').val(new Date(banner.start_date).toISOString().slice(0, 16));
                    } else {
                        $('#edit_start_date').val('');
                    }
                    
                    if (banner.end_date) {
                        $('#edit_end_date').val(new Date(banner.end_date).toISOString().slice(0, 16));
                    } else {
                        $('#edit_end_date').val('');
                    }
                    
                    // Show current image
                    if (banner.image_url) {
                        $('#editPreviewImage').attr('src', banner.image_url);
                        $('#editImagePreview').show();
                    }
                    
                    $('#editBannerModal').modal('show');
                }
            }).fail(function() {
                showAlert('error', 'Failed to load banner data');
            });
        });

        // Update banner
        $('#editBannerForm').on('submit', function(e) {
            e.preventDefault();
            const bannerId = $('#edit_banner_id').val();
            const formData = new FormData(this);
            
            $.ajax({
                url: `/admin/banners/${bannerId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#editBannerModal').modal('hide');
                        showAlert('success', 'Banner updated successfully!');
                        loadBanners();
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'An error occurred');
                }
            });
        });

        // View banner
        $(document).on('click', '.view-btn', function() {
            const bannerId = $(this).data('id');
            
            $.get(`/admin/banners/${bannerId}`, function(response) {
                if (response.success) {
                    const banner = response.data;
                    $('#view_title').text(banner.title);
                    $('#view_description').text(banner.description || 'N/A');
                    $('#view_url').html(banner.url ? `<a href="${banner.url}" target="_blank">${banner.url}</a>` : 'N/A');
                    $('#view_status').html(`<span class="badge ${banner.is_active ? 'badge-success' : 'badge-secondary'}">${banner.is_active ? 'Active' : 'Inactive'}</span>`);
                    $('#view_platform').text(banner.target_platform ? banner.target_platform.charAt(0).toUpperCase() + banner.target_platform.slice(1) : 'All');
                    
                    if (banner.start_date) {
                        $('#view_start_date').text(new Date(banner.start_date).toLocaleString());
                    } else {
                        $('#view_start_date').text('N/A');
                    }
                    
                    if (banner.end_date) {
                        $('#view_end_date').text(new Date(banner.end_date).toLocaleString());
                    } else {
                        $('#view_end_date').text('N/A');
                    }
                    
                    $('#view_created_at').text(new Date(banner.created_at).toLocaleString());
                    $('#view_updated_at').text(new Date(banner.updated_at).toLocaleString());
                    
                    if (banner.image_url) {
                        $('#view_image').attr('src', banner.image_url).show();
                    } else {
                        $('#view_image').hide();
                    }
                    
                    $('#viewBannerModal').modal('show');
                }
            }).fail(function() {
                showAlert('error', 'Failed to load banner data');
            });
        });

        // Delete banner confirmation
        $(document).on('click', '.delete-btn', function() {
            const bannerId = $(this).data('id');
            $('#delete_banner_id').val(bannerId);
            $('#deleteBannerModal').modal('show');
        });

        // Confirm delete
        $('#confirmDeleteBtn').on('click', function() {
            const bannerId = $('#delete_banner_id').val();
            
            $.ajax({
                url: `/admin/banners/${bannerId}`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        $('#deleteBannerModal').modal('hide');
                        showAlert('success', 'Banner deleted successfully!');
                        loadBanners();
                    }
                },
                error: function(xhr) {
                    showAlert('error', xhr.responseJSON?.message || 'An error occurred');
                }
            });
        });

        // Toggle banner status
        $(document).on('change', '.status-toggle', function() {
            const bannerId = $(this).data('id');
            const isActive = $(this).prop('checked');
            
            $.ajax({
                url: `/admin/banners/${bannerId}/toggle-status`,
                type: 'POST',
                data: {
                    _method: 'PATCH'
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Banner status updated!');
                    }
                },
                error: function(xhr) {
                    // Revert the toggle if there's an error
                    $(`#statusToggle${bannerId}`).prop('checked', !isActive);
                    showAlert('error', 'Failed to update banner status');
                }
            });
        });

        // Function to load banners
        function loadBanners(page = 1) {
            const search = $('#searchInput').val();
            const status = $('#statusFilter').val();
            const platform = $('#platformFilter').val();
            
            $.get('/admin/banners', {
                search: search,
                is_active: status,
                platform: platform,
                page: page
            }, function(response) {
                const banners = response.data;
                let html = '';
                
                if (banners.length > 0) {
                    banners.forEach((banner, index) => {
                        const statusClass = banner.is_active ? 'success' : 'secondary';
                        const statusText = banner.is_active ? 'Active' : 'Inactive';
                        const platformText = banner.target_platform ? banner.target_platform.charAt(0).toUpperCase() + banner.target_platform.slice(1) : 'All';
                        
                        html += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <img src="${banner.image_url}" alt="${banner.title}" style="width: 80px; height: 40px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td>${banner.title}</td>
                                <td>${platformText}</td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input status-toggle" id="statusToggle${banner.id}" 
                                            data-id="${banner.id}" ${banner.is_active ? 'checked' : ''}>
                                        <label class="custom-control-label" for="statusToggle${banner.id}"></label>
                                    </div>
                                </td>
                                <td>${banner.start_date ? new Date(banner.start_date).toLocaleDateString() : 'N/A'}</td>
                                <td>${banner.end_date ? new Date(banner.end_date).toLocaleDateString() : 'N/A'}</td>
                                <td>
                                    <button class="btn btn-sm btn-info view-btn mr-1" data-id="${banner.id}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary edit-btn mr-1" data-id="${banner.id}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="${banner.id}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    // Add pagination
                    let pagination = '<nav><ul class="pagination">';
                    
                    // Previous page link
                    if (response.current_page > 1) {
                        pagination += `<li class="page-item"><a class="page-link" href="#" data-page="${response.current_page - 1}">Previous</a></li>`;
                    } else {
                        pagination += '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
                    }
                    
                    // Page numbers
                    for (let i = 1; i <= response.last_page; i++) {
                        if (i === response.current_page) {
                            pagination += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                        } else if (i === 1 || i === response.last_page || (i >= response.current_page - 2 && i <= response.current_page + 2)) {
                            pagination += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        } else if (i === response.current_page - 3 || i === response.current_page + 3) {
                            pagination += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    // Next page link
                    if (response.current_page < response.last_page) {
                        pagination += `<li class="page-item"><a class="page-link" href="#" data-page="${response.current_page + 1}">Next</a></li>`;
                    } else {
                        pagination += '<li class="page-item disabled"><span class="page-link">Next</span></li>';
                    }
                    
                    pagination += '</ul></nav>';
                    
                    $('#pagination').html(pagination);
                } else {
                    html = '<tr><td colspan="8" class="text-center">No banners found</td></tr>';
                    $('#pagination').empty();
                }
                
                $('#bannersTable tbody').html(html);
            }).fail(function() {
                showAlert('error', 'Failed to load banners');
            });
        }

        // Handle pagination clicks
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) {
                loadBanners(page);
                $('html, body').animate({
                    scrollTop: $('.card').offset().top - 20
                }, 'fast');
            }
        });

        // Show alert message
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            
            // Remove any existing alerts
            $('.alert').remove();
            // Add new alert
            $('.container-fluid').prepend(alertHtml);
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    });
</script>
@endpush
