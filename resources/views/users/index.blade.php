@extends('layouts.app')

@section('title', 'User Management - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">User Management</h3>
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
            <a href="#">Users</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">User List</h4>
                    @can('create-users')
                    <button class="btn btn-primary btn-round ms-auto" onclick="openCreateForm()">
                        <i class="fa fa-plus"></i>
                        Add User
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="users-table" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th style="width: 10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->roles->isNotEmpty())
                                        <span class="badge badge-primary">{{ $user->roles->first()->name }}</span>
                                    @else
                                        <span class="badge badge-secondary">No Role</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="form-button-action">
                                        @can('edit-users')
                                        <button type="button" class="btn btn-link btn-primary btn-lg" onclick="openEditForm({{ $user->id }})" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @endcan
                                        @can('delete-users')
                                        @if($user->id != auth()->id())
                                        <button type="button" class="btn btn-link btn-danger" onclick="deleteUser({{ $user->id }})" title="Delete">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Form -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="userOffcanvas" aria-labelledby="userOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="userOffcanvasLabel">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="userForm">
            @csrf
            <input type="hidden" id="userId" name="user_id">
            <input type="hidden" id="formMethod" value="POST">

            <div class="mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback" id="nameError"></div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback" id="emailError"></div>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role" required>
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback" id="roleError"></div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger" id="passwordRequired">*</span></label>
                <input type="password" class="form-control" id="password" name="password">
                <small class="form-text text-muted" id="passwordHint">Leave empty to keep current password</small>
                <div class="invalid-feedback" id="passwordError"></div>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save User
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
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('userOffcanvas'));

    function openCreateForm() {
        document.getElementById('userOffcanvasLabel').innerText = 'Add User';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('password').required = true;
        document.getElementById('passwordRequired').style.display = 'inline';
        document.getElementById('passwordHint').style.display = 'none';
        clearErrors();
        offcanvas.show();
    }

    function openEditForm(userId) {
        document.getElementById('userOffcanvasLabel').innerText = 'Edit User';
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('password').required = false;
        document.getElementById('passwordRequired').style.display = 'none';
        document.getElementById('passwordHint').style.display = 'block';
        clearErrors();

        // Fetch user data
        fetch(`/users/${userId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('userId').value = data.data.id;
                document.getElementById('name').value = data.data.name;
                document.getElementById('email').value = data.data.email;
                document.getElementById('role').value = data.data.role;
                document.getElementById('password').value = '';
                document.getElementById('password_confirmation').value = '';
                offcanvas.show();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Failed to load user data', 'error');
        });
    }

    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const userId = document.getElementById('userId').value;
        const method = document.getElementById('formMethod').value;
        const url = userId ? `/users/${userId}` : '/users';
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

    function deleteUser(userId) {
        confirmAction('Are you sure you want to delete this user? This action cannot be undone.', function() {
            fetch(`/users/${userId}`, {
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
                showToast('Failed to delete user', 'error');
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
