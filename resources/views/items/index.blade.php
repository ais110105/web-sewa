@extends('layouts.app')

@section('title', 'Item Management - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Item Management</h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="{{ route('home') }}">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Items</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card modern-card">
            <div class="card-header border-0">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="card-title mb-0">Item List</h4>
                    @can('create-items')
                    <button class="btn btn-primary btn-round" onclick="openCreateForm()">
                        <i class="fa fa-plus"></i>
                        Add Item
                    </button>
                    @endcan
                </div>

                <!-- Filters Section -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="search-box">
                            <input type="text" id="searchInput" class="form-control form-control-lg" placeholder="Filter items...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="categoryFilter" class="form-select form-select-lg">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="statusFilter" class="form-select form-select-lg">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="rented">Rented</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-secondary btn-lg w-100" onclick="resetFilters()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="modern-table-container">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th style="width: 50px;">#</th>
                                <th>Image</th>
                                <th>Name <i class="fas fa-sort sort-icon" data-column="name"></i></th>
                                <th>Category</th>
                                <th>Price/Period <i class="fas fa-sort sort-icon" data-column="price"></i></th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            @forelse($items as $index => $item)
                            <tr data-category="{{ $item->category_id }}" data-status="{{ $item->status }}" data-name="{{ strtolower($item->name) }}" data-price="{{ $item->price_per_period }}">
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" value="{{ $item->id }}">
                                </td>
                                <td class="text-muted">{{ $items->firstItem() + $index }}</td>
                                <td>
                                    @if($item->photo_url)
                                        <img src="{{ Storage::url($item->photo_url) }}" alt="{{ $item->name }}" class="item-thumbnail">
                                    @else
                                        <div class="item-thumbnail-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ $item->name }}</td>
                                <td>
                                    <span class="badge badge-category">{{ $item->category?->name ?? '-' }}</span>
                                </td>
                                <td>Rp {{ number_format($item->price_per_period, 0, ',', '.') }}</td>
                                <td>
                                    <span class="stock-info">
                                        {{ $item->available_stock ?? 0 }}/{{ $item->stock ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->status === 'available')
                                        <span class="badge badge-status badge-success">Available</span>
                                    @elseif($item->status === 'rented')
                                        <span class="badge badge-status badge-warning">Rented</span>
                                    @else
                                        <span class="badge badge-status badge-secondary">Maintenance</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @can('edit-items')
                                        <button type="button" class="btn-action btn-action-edit" onclick="openEditForm({{ $item->id }})" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @endcan
                                        @can('delete-items')
                                        <button type="button" class="btn-action btn-action-delete" onclick="deleteItem({{ $item->id }})" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No items found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer with Selection Info and Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        <span id="selectionInfo">0 of {{ $items->total() }} row(s) selected.</span>
                    </div>
                    <div>
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Form -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="itemOffcanvas" aria-labelledby="itemOffcanvasLabel" style="width: 500px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="itemOffcanvasLabel">Add Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="itemForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" id="itemId" name="item_id">
            <input type="hidden" id="formMethod" value="POST">

            <!-- Image Upload -->
            <div class="mb-4">
                <label class="form-label">Item Image</label>
                <div class="image-upload-container">
                    <div class="image-preview" id="imagePreview">
                        <i class="fas fa-image fa-3x text-muted"></i>
                        <p class="text-muted mt-2">Click to upload image</p>
                    </div>
                    <input type="file" class="d-none" id="photo" name="photo" accept="image/*" onchange="previewImage(event)">
                    <button type="button" class="btn btn-outline-primary w-100 mt-2" onclick="document.getElementById('photo').click()">
                        <i class="fas fa-upload"></i> Choose Image
                    </button>
                    <small class="form-text text-muted">Max size: 2MB. Format: JPG, PNG, WEBP</small>
                </div>
                <div class="invalid-feedback" id="photoError"></div>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" id="category_idError"></div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Item Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback" id="nameError"></div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                <div class="invalid-feedback" id="descriptionError"></div>
            </div>

            <div class="mb-3">
                <label for="price_per_period" class="form-label">Price per Period <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="price_per_period" name="price_per_period" required min="0" step="0.01">
                <small class="form-text text-muted">Enter price in Rupiah</small>
                <div class="invalid-feedback" id="price_per_periodError"></div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="stock" class="form-label">Total Stock <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stock" name="stock" required min="0">
                    <div class="invalid-feedback" id="stockError"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="available_stock" class="form-label">Available Stock <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="available_stock" name="available_stock" required min="0">
                    <div class="invalid-feedback" id="available_stockError"></div>
                </div>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" id="status" name="status" required>
                    <option value="available">Available</option>
                    <option value="rented">Rented</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                <div class="invalid-feedback" id="statusError"></div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Item
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.modern-card {
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.search-box input {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}

.search-box input:focus {
    border-color: #177dff;
    box-shadow: 0 0 0 0.2rem rgba(23, 125, 255, 0.1);
}

.modern-table-container {
    overflow-x: auto;
}

.modern-table {
    margin-bottom: 0;
}

.modern-table thead {
    background-color: #f8f9fa;
}

.modern-table thead th {
    border-bottom: 2px solid #e9ecef;
    padding: 1rem;
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    border-bottom: 1px solid #f1f3f5;
    transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8f9fa;
}

.modern-table tbody td {
    padding: 1rem;
    vertical-align: middle;
}

.item-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.item-thumbnail-placeholder {
    width: 50px;
    height: 50px;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
}

.badge-category {
    background-color: #e7f3ff;
    color: #177dff;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.8125rem;
}

.badge-status {
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.8125rem;
}

.stock-info {
    color: #6c757d;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    border: none;
    background: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    color: #6c757d;
}

.btn-action:hover {
    background-color: #f8f9fa;
}

.btn-action-edit:hover {
    color: #177dff;
}

.btn-action-delete:hover {
    color: #f3545d;
}

.sort-icon {
    cursor: pointer;
    font-size: 0.75rem;
    margin-left: 0.25rem;
    opacity: 0.5;
    transition: opacity 0.2s ease;
}

.sort-icon:hover {
    opacity: 1;
}

.sort-icon.active {
    opacity: 1;
    color: #177dff;
}

.image-upload-container {
    margin-bottom: 1rem;
}

.image-preview {
    width: 100%;
    height: 200px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: #f8f9fa;
}

.image-preview:hover {
    border-color: #177dff;
    background-color: #f0f7ff;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 6px;
}

.image-preview.has-image {
    border-style: solid;
    border-color: #e9ecef;
    padding: 10px;
}

.empty-state {
    padding: 2rem;
}

.form-check-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('itemOffcanvas'));
    let currentImageUrl = null;

    // Image Preview
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('imagePreview');

        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                showToast('Image size must be less than 2MB', 'error');
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.classList.add('has-image');
            };
            reader.readAsDataURL(file);
        }
    }

    function openCreateForm() {
        document.getElementById('itemOffcanvasLabel').innerText = 'Add Item';
        document.getElementById('itemForm').reset();
        document.getElementById('itemId').value = '';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('imagePreview').innerHTML = `
            <i class="fas fa-image fa-3x text-muted"></i>
            <p class="text-muted mt-2">Click to upload image</p>
        `;
        document.getElementById('imagePreview').classList.remove('has-image');
        currentImageUrl = null;
        clearErrors();
        offcanvas.show();
    }

    function openEditForm(itemId) {
        document.getElementById('itemOffcanvasLabel').innerText = 'Edit Item';
        document.getElementById('formMethod').value = 'PUT';
        clearErrors();

        fetch(`/items/${itemId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('itemId').value = data.data.id;
                document.getElementById('category_id').value = data.data.category_id;
                document.getElementById('name').value = data.data.name;
                document.getElementById('description').value = data.data.description || '';
                document.getElementById('price_per_period').value = data.data.price_per_period;
                document.getElementById('stock').value = data.data.stock || 0;
                document.getElementById('available_stock').value = data.data.available_stock || 0;
                document.getElementById('status').value = data.data.status;

                // Preview existing image
                if (data.data.photo_url) {
                    currentImageUrl = data.data.photo_url;
                    document.getElementById('imagePreview').innerHTML = `<img src="${data.data.photo_url}" alt="Item Image">`;
                    document.getElementById('imagePreview').classList.add('has-image');
                }

                offcanvas.show();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Failed to load item data', 'error');
        });
    }

    document.getElementById('itemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const itemId = document.getElementById('itemId').value;
        const method = document.getElementById('formMethod').value;
        const url = itemId ? `/items/${itemId}` : '/items';
        const formData = new FormData(this);

        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                offcanvas.hide();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                if (data.errors) {
                    displayErrors(data.errors);
                } else {
                    showToast(data.message, 'error');
                }
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
        });
    });

    function deleteItem(itemId) {
        confirmAction('Are you sure you want to delete this item? This action cannot be undone.', function() {
            fetch(`/items/${itemId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Failed to delete item', 'error');
            });
        });
    }

    // Checkbox functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectionInfo();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('row-checkbox')) {
            updateSelectionInfo();
        }
    });

    function updateSelectionInfo() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        const total = checkboxes.length;
        document.getElementById('selectionInfo').textContent = `${checked} of ${total} row(s) selected.`;
    }

    // Filter functionality
    document.getElementById('searchInput').addEventListener('input', filterTable);
    document.getElementById('categoryFilter').addEventListener('change', filterTable);
    document.getElementById('statusFilter').addEventListener('change', filterTable);

    function filterTable() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const categoryFilter = document.getElementById('categoryFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('#itemsTableBody tr');

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const category = row.getAttribute('data-category');
            const status = row.getAttribute('data-status');

            const matchesSearch = !searchTerm || (name && name.includes(searchTerm));
            const matchesCategory = !categoryFilter || category === categoryFilter;
            const matchesStatus = !statusFilter || status === statusFilter;

            if (matchesSearch && matchesCategory && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('categoryFilter').value = '';
        document.getElementById('statusFilter').value = '';
        filterTable();
    }

    // Sorting functionality
    document.querySelectorAll('.sort-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const column = this.getAttribute('data-column');
            sortTable(column, this);
        });
    });

    let sortDirection = {};

    function sortTable(column, icon) {
        const tbody = document.getElementById('itemsTableBody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Toggle sort direction
        sortDirection[column] = sortDirection[column] === 'asc' ? 'desc' : 'asc';

        // Remove active class from all icons
        document.querySelectorAll('.sort-icon').forEach(i => i.classList.remove('active'));
        icon.classList.add('active');

        rows.sort((a, b) => {
            let aVal, bVal;

            if (column === 'name') {
                aVal = a.getAttribute('data-name');
                bVal = b.getAttribute('data-name');
            } else if (column === 'price') {
                aVal = parseFloat(a.getAttribute('data-price'));
                bVal = parseFloat(b.getAttribute('data-price'));
            }

            if (sortDirection[column] === 'asc') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal < bVal ? 1 : -1;
            }
        });

        rows.forEach(row => tbody.appendChild(row));
    }

    function displayErrors(errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const errorElement = document.getElementById(`${field}Error`);
            const inputElement = document.getElementById(field);

            if (errorElement && inputElement) {
                inputElement.classList.add('is-invalid');
                errorElement.textContent = messages[0];
                errorElement.style.display = 'block';
            }
        }
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
    }
</script>
@endpush
