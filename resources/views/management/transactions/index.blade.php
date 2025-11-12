@extends('layouts.app')

@section('title', 'Transaction Management - Web Sewa')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Transaction Management</h3>
    <ul class="breadcrumbs mb-3">
        <li class="nav-home">
            <a href="{{ route('dashboard') }}">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Transactions</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title">Daftar Transaksi & Rental</h4>
                    <div class="input-group" style="width: 300px;">
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari rental/user/order...">
                        <button class="btn btn-primary" type="button" id="searchBtn">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Tabs -->
                <ul class="nav nav-pills nav-secondary mb-3" id="filter-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-filter="all">
                            Semua <span class="badge bg-primary ms-1" id="count-all">{{ $rentals->total() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="confirmed">
                            Dikonfirmasi <span class="badge bg-info ms-1" id="count-confirmed">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="on_rent">
                            Berlangsung <span class="badge bg-primary ms-1" id="count-on_rent">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-filter="completed">
                            Selesai <span class="badge bg-success ms-1" id="count-completed">0</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-all">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="12%">Kode Rental</th>
                                        <th width="12%">Customer</th>
                                        <th width="18%">Periode & Item</th>
                                        <th width="10%">Total</th>
                                        <th width="12%">Status Rental</th>
                                        <th width="12%">Status Bayar</th>
                                        <th width="12%">Timeline</th>
                                        <th width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rentals as $rental)
                                    <tr class="rental-row"
                                        data-status="{{ $rental->status }}"
                                        data-payment="{{ $rental->payment_status }}">
                                        <td>
                                            <strong>{{ $rental->rental_code }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $rental->created_at->format('d M Y, H:i') }}
                                            </small>
                                            @if($rental->transaction)
                                            <br>
                                            <small class="text-muted">{{ $rental->transaction->order_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $rental->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $rental->user->email }}</small>
                                        </td>
                                        <td>
                                            <small>
                                                <strong>{{ $rental->start_date->format('d M') }} - {{ $rental->end_date->format('d M Y') }}</strong>
                                                ({{ $rental->duration_days }} hari)
                                            </small>
                                            <br>
                                            @if($rental->rentalItems->count() > 0)
                                                <small class="text-muted">
                                                    {{ $rental->rentalItems->first()->item->name }}
                                                    @if($rental->rentalItems->count() > 1)
                                                        +{{ $rental->rentalItems->count() - 1 }} item
                                                    @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                Rp {{ number_format($rental->total_price, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if($rental->status === 'pending')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            @elseif($rental->status === 'confirmed')
                                                <span class="badge badge-info">
                                                    <i class="fas fa-check"></i> Dikonfirmasi
                                                </span>
                                            @elseif($rental->status === 'on_rent')
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-box"></i> Berlangsung
                                                </span>
                                            @elseif($rental->status === 'completed')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-double"></i> Selesai
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rental->payment_status === 'paid')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> Lunas
                                                </span>
                                            @elseif($rental->payment_status === 'unpaid')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-exclamation-circle"></i> Belum Lunas
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-undo"></i> Refunded
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                @if($rental->confirmed_at)
                                                    <i class="fas fa-check text-info"></i> Konfirmasi<br>
                                                @endif
                                                @if($rental->picked_up_at)
                                                    <i class="fas fa-truck text-primary"></i> Diambil<br>
                                                @endif
                                                @if($rental->returned_at)
                                                    <i class="fas fa-check-double text-success"></i> Dikembalikan
                                                @endif
                                                @if(!$rental->confirmed_at && !$rental->picked_up_at && !$rental->returned_at)
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                <button type="button" class="btn btn-info btn-sm mb-1"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#detailOffcanvas{{ $rental->id }}"
                                                    title="Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm update-status-btn"
                                                    data-rental-id="{{ $rental->id }}"
                                                    data-current-status="{{ $rental->status }}"
                                                    title="Update Status">
                                                    <i class="fas fa-edit"></i> Update
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fa fa-clipboard-list fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">Belum ada transaksi</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $rentals->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Details -->
@foreach($rentals as $rental)
<div class="offcanvas offcanvas-end" tabindex="-1" id="detailOffcanvas{{ $rental->id }}" style="width: 500px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Detail Rental</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Informasi Customer -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Informasi Customer</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="40%" class="text-muted">Nama</td>
                    <td><strong>{{ $rental->user->name }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Email</td>
                    <td>{{ $rental->user->email }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Phone</td>
                    <td>{{ $rental->user->phone ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Informasi Rental -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Informasi Rental</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="40%" class="text-muted">Kode Rental</td>
                    <td><strong>{{ $rental->rental_code }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal Order</td>
                    <td>{{ $rental->created_at->format('d M Y, H:i') }} WIB</td>
                </tr>
                <tr>
                    <td class="text-muted">Periode Sewa</td>
                    <td>
                        {{ $rental->start_date->format('d M Y') }} -
                        {{ $rental->end_date->format('d M Y') }}
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Durasi</td>
                    <td><strong>{{ $rental->duration_days }} Hari</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Status</td>
                    <td>
                        @if($rental->status === 'confirmed')
                            <span class="badge badge-info">Dikonfirmasi</span>
                        @elseif($rental->status === 'on_rent')
                            <span class="badge badge-primary">Berlangsung</span>
                        @elseif($rental->status === 'completed')
                            <span class="badge badge-success">Selesai</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Informasi Pembayaran -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Informasi Pembayaran</h6>
            <table class="table table-sm table-borderless">
                @if($rental->transaction)
                <tr>
                    <td width="40%" class="text-muted">Order ID</td>
                    <td><strong>{{ $rental->transaction->order_id }}</strong></td>
                </tr>
                @endif
                <tr>
                    <td class="text-muted">Subtotal</td>
                    <td>Rp {{ number_format($rental->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Tax</td>
                    <td>Rp {{ number_format($rental->tax ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted"><strong>Total</strong></td>
                    <td><strong class="text-primary">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Status Bayar</td>
                    <td>
                        @if($rental->payment_status === 'paid')
                            <span class="badge badge-success">Lunas</span>
                        @elseif($rental->payment_status === 'unpaid')
                            <span class="badge badge-danger">Belum Lunas</span>
                        @else
                            <span class="badge badge-secondary">Refunded</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Item yang Disewa -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Item yang Disewa</h6>
            @foreach($rental->rentalItems as $item)
            <div class="card mb-2">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong class="d-block">{{ $item->item->name }}</strong>
                            <small class="text-muted">{{ $item->item->category?->name ?? '-' }}</small>
                        </div>
                        <div class="text-end">
                            <small class="d-block">{{ $item->quantity }}x</small>
                            <strong class="text-primary">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <small class="text-muted">Rp {{ number_format($item->price_per_day, 0, ',', '.') }}/hari</small>
                </div>
            </div>
            @endforeach
        </div>

        @if($rental->notes)
        <!-- Catatan -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Catatan</h6>
            <div class="alert alert-info mb-0">
                <small>{{ $rental->notes }}</small>
            </div>
        </div>
        @endif

        <!-- Timeline -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Timeline</h6>
            <div class="timeline-detail">
                @if($rental->transaction && $rental->transaction->paid_at)
                <div class="timeline-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <div>
                        <strong>Dibayar</strong>
                        <small class="d-block text-muted">{{ $rental->transaction->paid_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
                @endif
                @if($rental->confirmed_at)
                <div class="timeline-item">
                    <i class="fas fa-check-circle text-info"></i>
                    <div>
                        <strong>Dikonfirmasi</strong>
                        <small class="d-block text-muted">{{ $rental->confirmed_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
                @endif
                @if($rental->picked_up_at)
                <div class="timeline-item">
                    <i class="fas fa-truck text-primary"></i>
                    <div>
                        <strong>Diambil</strong>
                        <small class="d-block text-muted">{{ $rental->picked_up_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
                @endif
                @if($rental->returned_at)
                <div class="timeline-item">
                    <i class="fas fa-check-double text-success"></i>
                    <div>
                        <strong>Dikembalikan</strong>
                        <small class="d-block text-muted">{{ $rental->returned_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
                @endif
                @if(!$rental->transaction?->paid_at && !$rental->confirmed_at && !$rental->picked_up_at && !$rental->returned_at)
                <p class="text-muted mb-0">Belum ada aktivitas</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
    .timeline-detail {
        font-size: 0.9rem;
    }
    .timeline-item {
        padding: 12px 0;
        display: flex;
        align-items: start;
        gap: 12px;
        border-left: 2px solid #e9ecef;
        padding-left: 20px;
        margin-left: 10px;
        position: relative;
    }
    .timeline-item:last-child {
        border-left-color: transparent;
    }
    .timeline-item i {
        font-size: 1.2rem;
        position: absolute;
        left: -11px;
        background: white;
        padding: 2px;
    }
    .table td {
        vertical-align: middle;
    }
    .nav-pills .nav-link {
        border-radius: 5px;
        margin-right: 5px;
    }
    .offcanvas {
        max-width: 500px;
    }
</style>
@endpush

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
$(document).ready(function() {
    // Count and update badges on load
    function updateCounts() {
        const allCount = $('.rental-row').length;
        const confirmedCount = $('.rental-row[data-status="confirmed"]').length;
        const onRentCount = $('.rental-row[data-status="on_rent"]').length;
        const completedCount = $('.rental-row[data-status="completed"]').length;

        $('#count-all').text(allCount);
        $('#count-confirmed').text(confirmedCount);
        $('#count-on_rent').text(onRentCount);
        $('#count-completed').text(completedCount);
    }

    updateCounts();

    // Filter functionality
    $('#filter-tabs a').on('click', function(e) {
        e.preventDefault();

        // Update active tab
        $('#filter-tabs a').removeClass('active');
        $(this).addClass('active');

        const filter = $(this).data('filter');

        // Show/hide rows based on filter
        if (filter === 'all') {
            $('.rental-row').show();
        } else if (filter === 'confirmed') {
            $('.rental-row').hide();
            $('.rental-row[data-status="confirmed"]').show();
        } else if (filter === 'on_rent') {
            $('.rental-row').hide();
            $('.rental-row[data-status="on_rent"]').show();
        } else if (filter === 'completed') {
            $('.rental-row').hide();
            $('.rental-row[data-status="completed"]').show();
        }

        // Show empty message if no results
        const visibleRows = $('.rental-row:visible').length;
        if (visibleRows === 0) {
            if ($('.no-results-row').length === 0) {
                $('tbody').append(`
                    <tr class="no-results-row">
                        <td colspan="7" class="text-center py-4">
                            <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Tidak ada data untuk filter ini</p>
                        </td>
                    </tr>
                `);
            }
        } else {
            $('.no-results-row').remove();
        }
    });

    // Update Status Button
    $('.update-status-btn').on('click', function() {
        const rentalId = $(this).data('rental-id');
        const currentStatus = $(this).data('current-status');

        // Status options
        const statusOptions = {
            'confirmed': 'Dikonfirmasi',
            'on_rent': 'Berlangsung',
            'completed': 'Selesai'
        };

        let optionsHtml = '';
        for (const [value, label] of Object.entries(statusOptions)) {
            const selected = value === currentStatus ? 'selected' : '';
            optionsHtml += `<option value="${value}" ${selected}>${label}</option>`;
        }

        Swal.fire({
            title: 'Update Status Rental',
            html: `
                <select id="swal-status" class="form-control">
                    ${optionsHtml}
                </select>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            preConfirm: () => {
                return document.getElementById('swal-status').value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const newStatus = result.value;

                $.ajax({
                    url: `/transactions/${rentalId}/update-status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('Status berhasil diupdate', 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else {
                            showToast(response.message || 'Gagal update status', 'error');
                        }
                    },
                    error: function() {
                        showToast('Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
