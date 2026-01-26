@extends('layouts.app')

@section('title', 'Kelola Kategori - Tirta Kesuma')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Kelola Kategori</h3>
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
            <a href="#">Kategori</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Daftar Kategori</h4>
                    @can('create-categories')
                    <button class="btn btn-primary btn-round ms-auto" onclick="openCreateForm()">
                        <i class="fa fa-plus"></i>
                        Tambah Kategori
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="categories-table" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Deskripsi</th>
                                <th>Dibuat</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description ?? '-' }}</td>
                                <td>{{ $category->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="form-button-action">
                                        @can('edit-categories')
                                        <button type="button" class="btn btn-link btn-primary btn-lg" onclick="openEditForm({{ $category->id }})" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        @endcan
                                        @can('delete-categories')
                                        <button type="button" class="btn btn-link btn-danger" onclick="deleteCategory({{ $category->id }})" title="Delete">
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
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Form -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="categoryOffcanvas" aria-labelledby="categoryOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="categoryOffcanvasLabel">Tambah Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
    </div>
    <div class="offcanvas-body">
        <form id="categoryForm">
            @csrf
            <input type="hidden" id="categoryId" name="category_id">
            <input type="hidden" id="formMethod" value="POST">

            <div class="mb-3">
                <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required>
                <div class="invalid-feedback" id="nameError"></div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                <div class="invalid-feedback" id="descriptionError"></div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Simpan Kategori
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('categoryOffcanvas'));

    function openCreateForm() {
        document.getElementById('categoryOffcanvasLabel').innerText = 'Add Category';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('formMethod').value = 'POST';
        clearErrors();
        offcanvas.show();
    }

    function openEditForm(categoryId) {
        document.getElementById('categoryOffcanvasLabel').innerText = 'Edit Category';
        document.getElementById('formMethod').value = 'PUT';
        clearErrors();

        // Fetch category data
        fetch(`/categories/${categoryId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('categoryId').value = data.data.id;
                document.getElementById('name').value = data.data.name;
                document.getElementById('description').value = data.data.description || '';
                offcanvas.show();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            showToast('Failed to load category data', 'error');
        });
    }

    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const categoryId = document.getElementById('categoryId').value;
        const method = document.getElementById('formMethod').value;
        const url = categoryId ? `/categories/${categoryId}` : '/categories';
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

    function deleteCategory(categoryId) {
        confirmAction('Are you sure you want to delete this category? This action cannot be undone.', function() {
            fetch(`/categories/${categoryId}`, {
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
                showToast('Failed to delete category', 'error');
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
