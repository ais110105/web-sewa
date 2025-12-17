@extends('layouts.app')

@section('title', 'My Cart - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">My Cart</h3>
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
            <a href="#">My Cart</a>
        </li>
    </ul>
</div>

<div class="row">
    @if($cartItems->count() > 0)
    <!-- Cart Items -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Cart Items ({{ $cartItems->count() }})</h4>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearCart()">
                        <i class="fa fa-trash"></i> Clear Cart
                    </button>
                </div>
            </div>
            <div class="card-body">
                @foreach($cartItems as $cartItem)
                <div class="cart-item mb-3 p-3 border rounded" id="cart-item-{{ $cartItem->id }}">
                    <div class="row align-items-center">
                        <!-- Item Image -->
                        <div class="col-md-2">
                            <div class="cart-item-image">
                                @if($cartItem->item->photo_url)
                                    <img src="{{ asset('storage/' . $cartItem->item->photo_url) }}" alt="{{ $cartItem->item->name }}">
                                @else
                                    <div class="cart-placeholder">
                                        <i class="fa fa-box"></i>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Item Info -->
                        <div class="col-md-4">
                            <h6 class="mb-1">{{ $cartItem->item->name }}</h6>
                            <small class="text-muted">
                                <span class="badge-category-cart">{{ $cartItem->item->category?->name ?? 'Uncategorized' }}</span>
                            </small>
                            <p class="mb-0 mt-1">
                                <strong>Rp {{ number_format($cartItem->item->price_per_period, 0, ',', '.') }}</strong>
                                <small class="text-muted">/period</small>
                            </p>
                        </div>

                        <!-- Rental Period -->
                        <div class="col-md-3">
                            <small class="text-muted d-block">Rental Period</small>
                            <p class="mb-0">
                                <i class="fa fa-calendar"></i>
                                {{ $cartItem->start_date->format('d M Y') }}
                            </p>
                            <p class="mb-0">
                                <i class="fa fa-calendar"></i>
                                {{ $cartItem->end_date->format('d M Y') }}
                            </p>
                            <small class="badge-duration-cart">{{ $cartItem->duration_days }} days</small>
                        </div>

                        <!-- Quantity & Price -->
                        <div class="col-md-2">
                            <small class="text-muted d-block">Quantity</small>
                            <p class="mb-1"><strong>{{ $cartItem->quantity }}x</strong></p>
                            <small class="text-muted d-block mt-2">Subtotal</small>
                            <h6 class="text-primary mb-0">
                                Rp {{ number_format($cartItem->item->price_per_period * $cartItem->quantity, 0, ',', '.') }}
                            </h6>
                        </div>

                        <!-- Actions -->
                        <div class="col-md-1">
                            <button type="button" class="btn-remove-cart"
                                    onclick="removeCartItem({{ $cartItem->id }})"
                                    title="Remove">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Continue Shopping -->
        <div class="mt-3">
            <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Continue Shopping
            </a>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Order Summary</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5>Total</h5>
                    <h5 class="text-primary">Rp {{ number_format($total, 0, ',', '.') }}</h5>
                </div>
                <hr>

                <div class="alert alert-info">
                    <small>
                        <i class="fa fa-info-circle"></i>
                        You will be redirected to payment after checkout
                    </small>
                </div>

                <div class="alert alert-warning">
                    <small>
                        <strong>Sandbox Mode:</strong> Testing with Midtrans Sandbox.
                        <br>
                        If payment error occurs, check <strong>MIDTRANS_SETUP.md</strong>
                    </small>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="notes" rows="3"
                              placeholder="Add any special instructions..."></textarea>
                </div>

                <div class="d-grid">
                    <button type="button" class="btn btn-primary btn-lg" onclick="processCheckout()">
                        <i class="fa fa-credit-card"></i> Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="mb-2"><i class="fa fa-shield-alt text-success"></i> Secure Checkout</h6>
                <small class="text-muted">
                    Your payment information is secure and encrypted
                </small>
                <hr>
                <h6 class="mb-2"><i class="fa fa-clock text-info"></i> Flexible Rental</h6>
                <small class="text-muted">
                    Choose your rental period based on your needs
                </small>
            </div>
        </div>
    </div>
    @else
    <!-- Empty Cart -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa fa-shopping-cart fa-5x text-muted mb-3"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Browse our catalog and add items to your cart</p>
                <a href="{{ route('catalog.index') }}" class="btn btn-primary">
                    <i class="fa fa-shopping-bag"></i> Browse Catalog
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function removeCartItem(cartId) {
        confirmAction('Are you sure you want to remove this item from cart?', function() {
            fetch(`/cart/${cartId}`, {
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
                    // Remove item from view
                    document.getElementById(`cart-item-${cartId}`).remove();

                    // Reload page after 1 second to update totals
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Failed to remove item', 'error');
            });
        });
    }

    function clearCart() {
        confirmAction('Are you sure you want to clear your entire cart? This action cannot be undone.', function() {
            fetch('/cart', {
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
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Failed to clear cart', 'error');
            });
        });
    }

    function checkPaymentStatusFromCart(rentalId) {
        fetch(`/checkout/rental/${rentalId}/check-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, data.data.payment_status === 'paid' ? 'success' : 'info');
                setTimeout(() => {
                    window.location.href = '/checkout/history';
                }, 1500);
            } else {
                showToast(data.message || 'Failed to check payment status', 'error');
                setTimeout(() => {
                    window.location.href = '/checkout/history';
                }, 1500);
            }
        })
        .catch(error => {
            console.error('Check payment status error:', error);
            showToast('Failed to check payment status', 'error');
            setTimeout(() => {
                window.location.href = '/checkout/history';
            }, 1500);
        });
    }

    function processCheckout() {
        const notes = document.getElementById('notes').value;

        fetch('/checkout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Checkout successful! Redirecting to payment...', 'success');

                // Try to use Snap popup, fallback to redirect
                if (data.data.payment.snap_token) {
                    console.log('Payment info:', {
                        snap_token: data.data.payment.snap_token,
                        snap_url: data.data.payment.snap_url,
                        order_id: data.data.payment.order_id
                    });

                    setTimeout(() => {
                        // Check if snap is available
                        if (typeof window.snap !== 'undefined') {
                            console.log('Opening Midtrans Snap payment modal...');
                            const rentalId = data.data.rental.id;

                            window.snap.pay(data.data.payment.snap_token, {
                                onSuccess: function(result) {
                                    showToast('Payment successful! Updating status...', 'success');
                                    // Auto check payment status after success
                                    checkPaymentStatusFromCart(rentalId);
                                },
                                onPending: function(result) {
                                    showToast('Payment pending! Checking status...', 'info');
                                    // Auto check payment status after pending
                                    checkPaymentStatusFromCart(rentalId);
                                },
                                onError: function(result) {
                                    showToast('Payment failed!', 'error');
                                    // Redirect to history anyway to see the rental
                                    setTimeout(() => {
                                        window.location.href = '/checkout/history';
                                    }, 2000);
                                },
                                onClose: function() {
                                    showToast('Payment cancelled. You can continue payment from rental history.', 'warning');
                                    setTimeout(() => {
                                        window.location.href = '/checkout/history';
                                    }, 2000);
                                }
                            });
                        } else {
                            // Fallback: redirect to snap_url
                            console.log('Snap not available, redirecting to payment page');
                            window.location.href = data.data.payment.snap_url;
                        }
                    }, 1000);
                }
            } else {
                showToast(data.message || 'Checkout failed', 'error');
            }
        })
        .catch(error => {
            console.error('Checkout error:', error);
            showToast('An error occurred during checkout', 'error');
        });
    }
</script>

<!-- Midtrans Snap JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
@endpush

@push('styles')
<style>
.cart-item {
    transition: all 0.3s;
    border: 1px solid #e9ecef !important;
}

.cart-item:hover {
    background-color: #f8f9fa;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    border-color: #dee2e6 !important;
}

.cart-item-image {
    width: 100%;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8f9fa;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
}

.cart-placeholder i {
    font-size: 2rem;
    color: #adb5bd;
}

.badge-category-cart {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    background: #f8f9fa;
    color: #495057;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-duration-cart {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    background: #e9ecef;
    color: #495057;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.btn-remove-cart {
    border: none;
    background: #f8f9fa;
    color: #6c757d;
    padding: 0.5rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-remove-cart:hover {
    background: #dc3545;
    color: #ffffff;
}

.btn-outline-secondary {
    border-color: #dee2e6;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    color: #495057;
}

.card {
    border: 1px solid #e9ecef;
}
</style>
@endpush
