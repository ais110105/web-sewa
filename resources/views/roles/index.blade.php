@extends('layouts.app')

@section('title', 'Role Management - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Role Management</h3>
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
            <a href="#">Roles</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Role List</h4>
                    @can('create-roles')
                    <button class="btn btn-primary btn-round ms-auto" onclick="openCreateForm()">
                        <i class="fa fa-plus"></i>
                        Add Role
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Permissions Count</th>
                                <th>Users Count</th>
                                <th>Created At</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr>
                                <td><span class="badge badge-primary">{{ ucfirst($role->name) }}</span></td>
                                <td>{{ $role->permissions_count }} permissions</td>
                                <td>{{ $role->users_count }} users</td>
                                <td>{{ $role->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="form-button-action">
                                        @can('edit-roles')
                                        <button type="button" class="btn btn-link btn-primary btn-lg" onclick="openEditForm({{ $role->id }})" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @endcan
                                        @can('delete-roles')
                                        <button type="button" class="btn btn-link btn-danger" onclick="deleteRole({{ $role->id }})" title="Delete">
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
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Form -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="roleOffcanvas" style="width: 500px;" aria-labelledby="roleOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="roleOffcanvasLabel">Add Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="roleForm">
            @csrf
            <input type="hidden" id="roleId" name="role_id">
            <input type="hidden" id="formMethod" value="POST">

            <div class="mb-3">
                <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., manager">
                <small class="form-text text-muted">Use lowercase, no spaces</small>
                <div class="invalid-feedback" id="nameError"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Permissions</label>
                <div class="permissions-container" style="max-height: 400px; overflow-y: auto;">
                    @foreach($permissions as $group => $perms)
                    <div class="mb-3">
                        <h6 class="fw-bold">{{ ucfirst($group) }}</h6>
                        @foreach($perms as $permission)
                        <div class="form-check">
                            <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                {{ ucwords(str_replace('-', ' ', $permission->name)) }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Role
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
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('roleOffcanvas'));

    function openCreateForm() {
        document.getElementById('roleOffcanvasLabel').innerText = 'Add Role';
        document.getElementById('roleForm').reset();
        document.getElementById('roleId').value = '';
        document.getElementById('formMethod').value = 'POST';
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        clearErrors();
        offcanvas.show();
    }

    function openEditForm(roleId) {
        document.getElementById('roleOffcanvasLabel').innerText = 'Edit Role';
        document.getElementById('formMethod').value = 'PUT';
        clearErrors();

        fetch(`/roles/${roleId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('roleId').value = data.data.id;
                document.getElementById('name').value = data.data.name;

                // Uncheck all first
                document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);

                // Check permissions
                data.data.permissions.forEach(permission => {
                    const checkbox = document.querySelector(`input[value="${permission}"]`);
                    if (checkbox) checkbox.checked = true;
                });

                offcanvas.show();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Failed to load role data', 'error');
        });
    }

    document.getElementById('roleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const roleId = document.getElementById('roleId').value;
        const method = document.getElementById('formMethod').value;
        const url = roleId ? `/roles/${roleId}` : '/roles';
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

    function deleteRole(roleId) {
        confirmAction('Are you sure you want to delete this role? This action cannot be undone.', function() {
            fetch(`/roles/${roleId}`, {
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
                showToast('Failed to delete role', 'error');
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
