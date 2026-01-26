@extends('layouts.app')

@section('title', 'Profil Saya - Tirta Kesuma')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Profil Saya</h3>
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
            <a href="#">Profil</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Informasi Profil</div>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    @csrf
                    @method('PUT')

                    <!-- Account Information -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h5 class="text-primary">Informasi Akun</h5>
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->name }}" disabled>
                            <small class="form-text text-muted">Hubungi admin untuk mengubah nama Anda</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ Auth::user()->email }}" disabled>
                            <small class="form-text text-muted">Hubungi admin untuk mengubah email Anda</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peran</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->roles->first()?->name ?? 'Tidak ada peran' }}" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bergabung Sejak</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->created_at->format('d M Y') }}" disabled>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="row mt-4">
                        <div class="col-md-12 mb-3">
                            <h5 class="text-primary">Informasi Pribadi</h5>
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="{{ $profile?->full_name ?? '' }}" placeholder="Masukkan nama lengkap Anda">
                            <div class="invalid-feedback" id="full_nameError"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ $profile?->phone ?? '' }}" placeholder="+62 812 3456 7890">
                            <div class="invalid-feedback" id="phoneError"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3" placeholder="Masukkan alamat Anda">{{ $profile?->address ?? '' }}</textarea>
                            <div class="invalid-feedback" id="addressError"></div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan Profil
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const formData = new FormData(this);

        fetch('{{ route("profile.update") }}', {
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
            } else {
                if (data.errors) {
                    displayErrors(data.errors);
                } else {
                    showToast(data.message, 'error');
                }
            }
        })
        .catch(error => {
            showToast('An error occurred while updating profile', 'error');
        });
    });

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
