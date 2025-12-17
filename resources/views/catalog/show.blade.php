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

<div class="row g-4">
    <!-- Left Side - Item Detail -->
    <div class="col-lg-8">
        <div class="detail-card">
            <div class="row g-4">
                <!-- Image -->
                <div class="col-md-6">
                    <div class="detail-image-container">
                        @if($item->photo_url)
                            <img src="{{ asset('storage/' . $item->photo_url) }}" alt="{{ $item->name }}" class="detail-image">
                        @else
                            <div class="detail-placeholder">
                                <i class="fas fa-box"></i>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Info -->
                <div class="col-md-6">
                    <div class="detail-info">
                        <span class="detail-category">{{ $item->category?->name ?? 'Uncategorized' }}</span>
                        <h2 class="detail-title">{{ $item->name }}</h2>

                        @if($item->status === 'available')
                            <span class="status-badge-detail available">
                                <i class="fas fa-check-circle"></i> Available
                            </span>
                        @else
                            <span class="status-badge-detail unavailable">
                                <i class="fas fa-times-circle"></i> Not Available
                            </span>
                        @endif

                        <div class="price-section">
                            <span class="price-label">Price per Period</span>
                            <div class="price-amount">Rp {{ number_format($item->price_per_period, 0, ',', '.') }}</div>
                        </div>

                        @if($item->stock)
                        <div class="stock-info">
                            <span class="stock-label">Stock Available:</span>
                            <span class="stock-value">{{ $item->available_stock ?? 0 }} / {{ $item->stock }}</span>
                        </div>
                        @endif

                        <div class="description-section">
                            <h5 class="section-title">Description</h5>
                            <p class="description-text">
                                {{ $item->description ?? 'No description available.' }}
                            </p>
                        </div>

                        <div class="action-buttons">
                            <button type="button" class="btn-add-cart" onclick="scrollToRentalForm()">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                            <a href="{{ route('catalog.index') }}" class="btn-back">
                                <i class="fas fa-arrow-left"></i> Back to Catalog
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side - Rental Form -->
    <div class="col-lg-4">
        <div class="rental-card" id="rentalForm">
            <h4 class="rental-title">Rental Information</h4>

            <form id="addToCartForm">
                @csrf
                <input type="hidden" name="item_id" value="{{ $item->id }}">

                <div class="form-group-custom">
                    <label class="form-label-custom">Quantity</label>
                    <input type="number" class="form-input-custom" id="quantity" name="quantity"
                           value="1" min="1" max="{{ $item->available_stock ?? 1 }}" required>
                    <div class="invalid-feedback" id="quantityError"></div>
                </div>

                <div class="form-group-custom">
                    <label class="form-label-custom">Start Date</label>
                    <input type="date" class="form-input-custom" id="start_date" name="start_date"
                           min="{{ date('Y-m-d') }}" required>
                    <div class="invalid-feedback" id="start_dateError"></div>
                </div>

                <div class="form-group-custom">
                    <label class="form-label-custom">End Date</label>
                    <input type="date" class="form-input-custom" id="end_date" name="end_date"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    <div class="invalid-feedback" id="end_dateError"></div>
                </div>

                <div class="duration-info" id="durationInfo" style="display: none;">
                    <div class="duration-row">
                        <span>Duration:</span>
                        <span class="duration-value"><span id="durationDays">0</span> days</span>
                    </div>
                </div>

                <div class="price-summary">
                    <div class="summary-row">
                        <span>Price per Period:</span>
                        <span class="summary-value">Rp {{ number_format($item->price_per_period, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Quantity:</span>
                        <span class="summary-value" id="quantityDisplay">1</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit-rental">
                    <i class="fas fa-plus"></i> Add to Cart
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Related Items -->
@if($relatedItems->count() > 0)
<div class="related-section">
    <h4 class="related-title">Related Items</h4>
    <div class="related-grid">
        @foreach($relatedItems as $relatedItem)
        <a href="{{ route('catalog.show', $relatedItem->id) }}" class="related-item-card">
            <div class="related-image">
                @if($relatedItem->photo_url)
                    <img src="{{ asset('storage/' . $relatedItem->photo_url) }}" alt="{{ $relatedItem->name }}">
                @else
                    <div class="related-placeholder">
                        <i class="fas fa-box"></i>
                    </div>
                @endif
            </div>
            <div class="related-content">
                <h6 class="related-name">{{ $relatedItem->name }}</h6>
                <p class="related-price">Rp {{ number_format($relatedItem->price_per_period, 0, ',', '.') }}</p>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
/* Detail Card */
.detail-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 2rem;
}

.detail-image-container {
    width: 100%;
    height: 400px;
    border-radius: 12px;
    overflow: hidden;
    background: #f8f9fa;
}

.detail-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.detail-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
}

.detail-placeholder i {
    font-size: 5rem;
    color: #adb5bd;
}

/* Detail Info */
.detail-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-category {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    background: #f0f7ff;
    color: #177dff;
    border-radius: 6px;
    font-size: 0.8125rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: fit-content;
}

.detail-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    margin: 0;
    line-height: 1.3;
}

.status-badge-detail {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    width: fit-content;
}

.status-badge-detail.available {
    background: #d4edda;
    color: #155724;
}

.status-badge-detail.unavailable {
    background: #f8d7da;
    color: #721c24;
}

.price-section {
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
    margin: 1rem 0;
}

.price-label {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.price-amount {
    font-size: 2rem;
    font-weight: 700;
    color: #177dff;
    line-height: 1;
}

.stock-info {
    padding: 0.75rem 1rem;
    background: #fff3cd;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stock-label {
    font-size: 0.875rem;
    color: #856404;
}

.stock-value {
    font-weight: 600;
    color: #856404;
}

.description-section {
    margin: 1.5rem 0;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.75rem;
}

.description-text {
    font-size: 0.9375rem;
    color: #6c757d;
    line-height: 1.7;
    margin: 0;
}

.action-buttons {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.btn-add-cart {
    flex: 1;
    padding: 0.875rem 1.5rem;
    background: #177dff;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-add-cart:hover {
    background: #0c5fd7;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(23, 125, 255, 0.3);
}

.btn-back {
    padding: 0.875rem 1.5rem;
    background: #ffffff;
    color: #6c757d;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-back:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #adb5bd;
}

/* Rental Card */
.rental-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.75rem;
    position: sticky;
    top: 2rem;
}

.rental-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 1.5rem;
}

.form-group-custom {
    margin-bottom: 1.25rem;
}

.form-label-custom {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-input-custom {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    font-size: 0.9375rem;
    transition: all 0.2s ease;
}

.form-input-custom:focus {
    outline: none;
    border-color: #177dff;
    box-shadow: 0 0 0 3px rgba(23, 125, 255, 0.1);
}

.form-input-custom.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: none;
    font-size: 0.8125rem;
    color: #dc3545;
    margin-top: 0.25rem;
}

.duration-info {
    padding: 1rem;
    background: #e7f3ff;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.duration-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
    color: #0c5fd7;
}

.duration-value {
    font-weight: 600;
}

.price-summary {
    padding: 1.25rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1.25rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9375rem;
    color: #495057;
    margin-bottom: 0.75rem;
}

.summary-row:last-child {
    margin-bottom: 0;
}

.summary-value {
    font-weight: 600;
    color: #212529;
}

.btn-submit-rental {
    width: 100%;
    padding: 0.875rem;
    background: #177dff;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-submit-rental:hover {
    background: #0c5fd7;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(23, 125, 255, 0.3);
}

/* Related Section */
.related-section {
    margin-top: 3rem;
}

.related-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 1.5rem;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.25rem;
}

.related-item-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    transition: all 0.3s ease;
    display: block;
}

.related-item-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.related-image {
    width: 100%;
    height: 160px;
    background: #f8f9fa;
    overflow: hidden;
}

.related-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
}

.related-placeholder i {
    font-size: 2.5rem;
    color: #adb5bd;
}

.related-content {
    padding: 1rem;
}

.related-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
}

.related-price {
    font-size: 0.875rem;
    font-weight: 700;
    color: #177dff;
    margin: 0;
}

/* Responsive */
@media (max-width: 991px) {
    .rental-card {
        position: static;
    }

    .detail-image-container {
        height: 300px;
    }
}

@media (max-width: 768px) {
    .detail-card {
        padding: 1.5rem;
    }

    .detail-title {
        font-size: 1.5rem;
    }

    .price-amount {
        font-size: 1.5rem;
    }

    .action-buttons {
        flex-direction: column;
    }

    .related-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
@endpush

@push('scripts')
<script>
function scrollToRentalForm() {
    document.getElementById('rentalForm').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

// Update quantity display
document.getElementById('quantity').addEventListener('input', function() {
    document.getElementById('quantityDisplay').textContent = this.value;
});

// Calculate duration
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
            this.reset();
            document.getElementById('durationInfo').style.display = 'none';
            document.getElementById('quantityDisplay').textContent = '1';

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
