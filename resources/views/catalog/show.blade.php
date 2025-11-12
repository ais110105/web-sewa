@extends('layouts.app')

@section('title', $item->name . ' - Catalog')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Item Detail</h3>
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
            <a href="{{ route('catalog.index') }}">Catalog</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">{{ $item->name }}</a>
        </li>
    </ul>
</div>

<div class="row">
    <!-- Item Detail -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Image -->
                    <div class="col-md-5">
                        <div class="bg-light d-flex align-items-center justify-content-center rounded"
                             style="height: 300px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fa fa-box fa-5x text-white"></i>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="col-md-7">
                        <span class="badge badge-info mb-2">
                            {{ $item->category?->name ?? 'Uncategorized' }}
                        </span>
                        <h3 class="fw-bold">{{ $item->name }}</h3>

                        <div class="mb-3">
                            @if($item->status === 'available')
                                <span class="badge badge-success">
                                    <i class="fa fa-check-circle"></i> Available
                                </span>
                            @else
                                <span class="badge badge-secondary">Not Available</span>
                            @endif
                        </div>

                        <div class="price-box bg-light p-3 rounded mb-3">
                            <small class="text-muted d-block">Price per Period</small>
                            <h2 class="text-primary mb-0">
                                Rp {{ number_format($item->price_per_period, 0, ',', '.') }}
                            </h2>
                        </div>

                        <h5 class="mb-2">Description</h5>
                        <p class="text-muted">
                            {{ $item->description ?? 'No description available.' }}
                        </p>

                        <div class="d-grid gap-2 mt-4">
                            <button type="button" class="btn btn-primary btn-lg" onclick="openRentalForm()">
                                <i class="fa fa-shopping-cart"></i> Add to Cart
                            </button>
                            <a href="{{ route('catalog.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Back to Catalog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Rental Information</h5>
            </div>
            <div class="card-body">
                <form id="addToCartForm">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity"
                               value="1" min="1" required>
                        <div class="invalid-feedback" id="quantityError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               min="{{ date('Y-m-d') }}" required>
                        <div class="invalid-feedback" id="start_dateError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        <div class="invalid-feedback" id="end_dateError"></div>
                    </div>

                    <div class="alert alert-info" id="durationInfo" style="display: none;">
                        <small>
                            <strong>Duration:</strong> <span id="durationDays">0</span> days
                        </small>
                    </div>

                    <div class="alert alert-success">
                        <small>
                            <strong>Price:</strong> Rp {{ number_format($item->price_per_period, 0, ',', '.') }} × Quantity
                        </small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add to Cart
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Related Items -->
@if($relatedItems->count() > 0)
<div class="row mt-4">
    <div class="col-md-12">
        <h4 class="fw-bold mb-3">Related Items</h4>
        <div class="row">
            @foreach($relatedItems as $relatedItem)
            <div class="col-md-3 mb-3">
                <div class="card h-100">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                         style="height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fa fa-box fa-3x text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title">{{ $relatedItem->name }}</h6>
                        <p class="text-primary mb-2">
                            Rp {{ number_format($relatedItem->price_per_period, 0, ',', '.') }}
                        </p>
                        <a href="{{ route('catalog.show', $relatedItem->id) }}" class="btn btn-sm btn-primary">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Calculate duration only (no price calculation needed)
    function calculateDuration() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const duration = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

            if (duration > 0) {
                document.getElementById('durationDays').textContent = duration;
                document.getElementById('durationInfo').style.display = 'block';
            } else {
                document.getElementById('durationInfo').style.display = 'none';
            }
        }
    }

    // Event listeners
    document.getElementById('start_date').addEventListener('change', calculateDuration);
    document.getElementById('end_date').addEventListener('change', calculateDuration);

    // Add to cart form submission
    document.getElementById('addToCartForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const formData = new FormData(this);

        fetch('{{ route('cart.store') }}', {
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
                // Reset form
                this.reset();
                document.getElementById('durationInfo').style.display = 'none';

                // Redirect to cart after 1.5 seconds
                setTimeout(() => {
                    window.location.href = '{{ route('cart.index') }}';
                }, 1500);
            } else {
                if (data.errors) {
                    displayErrors(data.errors);
                } else {
                    showToast(data.message || 'Failed to add item to cart', 'error');
                }
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
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
