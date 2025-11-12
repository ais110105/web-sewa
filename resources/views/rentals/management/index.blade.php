@extends('layouts.app')

@section('title', 'Rental Management - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Rental Management</h3>
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
            <a href="#">Rental Management</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <!-- Filter Card -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('rentals.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control" name="search" placeholder="Rental code or user name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="on_rent" {{ request('status') == 'on_rent' ? 'selected' : '' }}>On Rent</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Status</label>
                        <select class="form-select" name="payment_status">
                            <option value="all" {{ request('payment_status') == 'all' ? 'selected' : '' }}>All</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fa fa-search"></i> Filter
                        </button>
                        <a href="{{ route('rentals.index') }}" class="btn btn-secondary">
                            <i class="fa fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rentals List -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">All Rentals ({{ $rentals->total() }})</h4>
            </div>
            <div class="card-body">
                @if($rentals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rental Code</th>
                                <th>Customer</th>
                                <th>Period</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rentals as $rental)
                            <tr>
                                <td>
                                    <strong>{{ $rental->rental_code }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $rental->created_at->format('d M Y, H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $rental->user->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $rental->user->email }}</small>
                                </td>
                                <td>
                                    <small>
                                        {{ $rental->start_date->format('d M Y') }}<br>
                                        {{ $rental->end_date->format('d M Y') }}<br>
                                        <span class="badge badge-info">{{ $rental->duration_days }} days</span>
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        {{ $rental->rentalItems->count() }} item(s)
                                        <br>
                                        @foreach($rental->rentalItems->take(2) as $item)
                                            {{ $item->item->name }}@if(!$loop->last),@endif
                                        @endforeach
                                        @if($rental->rentalItems->count() > 2)
                                            <br>+{{ $rental->rentalItems->count() - 2 }} more
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <strong>Rp {{ number_format($rental->total_price, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @if($rental->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($rental->status === 'confirmed')
                                        <span class="badge badge-info">Confirmed</span>
                                    @elseif($rental->status === 'on_rent')
                                        <span class="badge badge-primary">On Rent</span>
                                    @elseif($rental->status === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-secondary">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rental->payment_status === 'paid')
                                        <span class="badge badge-success">Paid</span>
                                    @else
                                        <span class="badge badge-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('rentals.show', $rental) }}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if(in_array($rental->status, ['confirmed', 'on_rent']))
                                        <button type="button" class="btn btn-sm btn-success" onclick="updateStatus({{ $rental->id }}, 'completed')" title="Mark as Returned">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $rentals->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fa fa-clipboard-list fa-5x text-muted mb-3"></i>
                    <h4>No rentals found</h4>
                    <p class="text-muted">Try adjusting your filters</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Rental Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateStatusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select class="form-select" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="on_rent">On Rent</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateStatus(rentalId, status) {
    if (status === 'completed') {
        confirmAction('Mark this rental as returned? Stock will be increased automatically.', function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/rentals/${rentalId}/update-status`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;

            form.appendChild(csrfToken);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        });
    }
}
</script>
@endpush
