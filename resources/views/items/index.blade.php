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
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Item List</h4>
                    @can('create-items')
                    <button class="btn btn-primary btn-round ms-auto" onclick="openCreateForm()">
                        <i class="fa fa-plus"></i>
                        Add Item
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="items-table" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price/Period</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td><span class="badge badge-info">{{ $item->category?->name ?? '-' }}</span></td>
                                <td>Rp {{ number_format($item->price_per_period, 0, ',', '.') }}</td>
                                <td>
                                    @if($item->status === 'available')
                                        <span class="badge badge-success">Available</span>
                                    @elseif($item->status === 'rented')
                                        <span class="badge badge-warning">Rented</span>
                                    @else
                                        <span class="badge badge-secondary">Maintenance</span>
                                    @endif
                                </td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="form-button-action">
                                        @can('edit-items')
                                        <button type="button" class="btn btn-link btn-primary btn-lg" onclick="openEditForm({{ $item->id }})" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @endcan
                                        @can('delete-items')
                                        <button type="button" class="btn btn-link btn-danger" onclick="deleteItem({{ $item->id }})" title="Delete">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Form -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="itemOffcanvas" aria-labelledby="itemOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="itemOffcanvasLabel">Add Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="itemForm">
            @csrf
            <input type="hidden" id="itemId" name="item_id">
            <input type="hidden" id="formMethod" value="POST">

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

@push('scripts')
<script>
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('itemOffcanvas'));

    function openCreateForm() {
        document.getElementById('itemOffcanvasLabel').innerText = 'Add Item';
        document.getElementById('itemForm').reset();
        document.getElementById('itemId').value = '';
        document.getElementById('formMethod').value = 'POST';
        clearErrors();
        offcanvas.show();
    }

    function openEditForm(itemId) {
        document.getElementById('itemOffcanvasLabel').innerText = 'Edit Item';
        document.getElementById('formMethod').value = 'PUT';
        clearErrors();

        // Fetch item data
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
                document.getElementById('status').value = data.data.status;
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
