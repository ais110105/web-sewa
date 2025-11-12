@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Riwayat Transaksi</h3>
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
            <a href="#">Riwayat Transaksi</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Daftar Transaksi</h4>
                    <a href="{{ route('payment.page') }}" class="btn btn-primary btn-sm ms-auto">
                        <i class="fas fa-plus"></i> Transaksi Baru
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Tabs -->
                <ul class="nav nav-pills nav-secondary mb-3" id="filter-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" data-filter="all">
                            Semua <span class="badge bg-primary ms-1" id="count-all">{{ $transactions->total() }}</span>
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
                                        <th width="15%">Order ID</th>
                                        <th width="15%">Kode Rental</th>
                                        <th width="20%">Periode & Item</th>
                                        <th width="12%">Total</th>
                                        <th width="13%">Status Rental</th>
                                        <th width="13%">Status Bayar</th>
                                        <th width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                    <tr class="transaction-row"
                                        data-status="{{ $transaction->status }}"
                                        data-rental-status="{{ $transaction->rental ? $transaction->rental->status : '' }}">
                                        <td>
                                            <strong>{{ $transaction->order_id }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $transaction->created_at->format('d M Y, H:i') }}</small>
                                        </td>
                                        <td>
                                            @if($transaction->rental)
                                                <strong>{{ $transaction->rental->rental_code }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $transaction->rental->duration_days }} hari
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->rental)
                                                <small>
                                                    {{ $transaction->rental->start_date->format('d M') }} -
                                                    {{ $transaction->rental->end_date->format('d M Y') }}
                                                </small>
                                                <br>
                                                @if($transaction->rental->rentalItems->count() > 0)
                                                    <small class="text-muted">
                                                        {{ $transaction->rental->rentalItems->first()->item->name }}
                                                        @if($transaction->rental->rentalItems->count() > 1)
                                                            +{{ $transaction->rental->rentalItems->count() - 1 }} lainnya
                                                        @endif
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}
                                            </strong>
                                            <br>
                                            <small>
                                                @if($transaction->payment_method === 'qris')
                                                    <span class="badge badge-info badge-sm">QRIS</span>
                                                @elseif($transaction->payment_method === 'bank_transfer')
                                                    <span class="badge badge-primary badge-sm">
                                                        VA {{ strtoupper($transaction->bank ?? '') }}
                                                    </span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            @if($transaction->rental)
                                                @if($transaction->rental->status === 'pending')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @elseif($transaction->rental->status === 'confirmed')
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-check"></i> Dikonfirmasi
                                                    </span>
                                                @elseif($transaction->rental->status === 'on_rent')
                                                    <span class="badge badge-primary">
                                                        <i class="fas fa-box"></i> Berlangsung
                                                    </span>
                                                @elseif($transaction->rental->status === 'completed')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-double"></i> Selesai
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->status === 'pending')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-hourglass-half"></i> Pending
                                                </span>
                                            @elseif($transaction->status === 'settlement')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> Lunas
                                                </span>
                                            @elseif($transaction->status === 'expire')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-clock"></i> Expired
                                                </span>
                                            @elseif($transaction->status === 'cancel')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times-circle"></i> Batal
                                                </span>
                                            @elseif($transaction->status === 'deny')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-ban"></i> Ditolak
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info"
                                                    data-bs-toggle="offcanvas"
                                                    data-bs-target="#detailOffcanvas{{ $transaction->id }}"
                                                    title="Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($transaction->isPending())
                                                <button type="button" class="btn btn-warning btn-sm check-status-btn"
                                                    data-order-id="{{ $transaction->order_id }}" title="Cek Status">
                                                    <i class="fas fa-sync"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm cancel-btn"
                                                    data-order-id="{{ $transaction->order_id }}" title="Batalkan">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">Belum ada transaksi</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Details -->
@foreach($transactions as $transaction)
<div class="offcanvas offcanvas-end" tabindex="-1" id="detailOffcanvas{{ $transaction->id }}" style="width: 500px;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Detail Transaksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <!-- Informasi Transaksi -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Informasi Transaksi</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="40%" class="text-muted">Order ID</td>
                    <td><strong>{{ $transaction->order_id }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal</td>
                    <td>{{ $transaction->created_at->format('d M Y, H:i') }} WIB</td>
                </tr>
                <tr>
                    <td class="text-muted">Total</td>
                    <td><strong class="text-primary">Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Metode</td>
                    <td>
                        @if($transaction->payment_method === 'qris')
                            QRIS
                        @elseif($transaction->payment_method === 'bank_transfer')
                            VA {{ strtoupper($transaction->bank ?? 'Bank') }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Status</td>
                    <td>
                        @if($transaction->status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($transaction->status === 'settlement')
                            <span class="badge badge-success">Lunas</span>
                        @elseif($transaction->status === 'expire')
                            <span class="badge badge-danger">Expired</span>
                        @elseif($transaction->status === 'cancel')
                            <span class="badge badge-danger">Dibatalkan</span>
                        @elseif($transaction->status === 'deny')
                            <span class="badge badge-danger">Ditolak</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @if($transaction->rental)
        <!-- Informasi Rental -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Informasi Rental</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <td width="40%" class="text-muted">Kode Rental</td>
                    <td><strong>{{ $transaction->rental->rental_code }}</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Periode</td>
                    <td>
                        {{ $transaction->rental->start_date->format('d M Y') }} -
                        {{ $transaction->rental->end_date->format('d M Y') }}
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Durasi</td>
                    <td><strong>{{ $transaction->rental->duration_days }} Hari</strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Status Rental</td>
                    <td>
                        @if($transaction->rental->status === 'confirmed')
                            <span class="badge badge-info">Dikonfirmasi</span>
                        @elseif($transaction->rental->status === 'on_rent')
                            <span class="badge badge-primary">Berlangsung</span>
                        @elseif($transaction->rental->status === 'completed')
                            <span class="badge badge-success">Selesai</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Item yang Disewa -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Item yang Disewa</h6>
            @foreach($transaction->rental->rentalItems as $item)
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

        <!-- Timeline -->
        <div class="mb-4">
            <h6 class="text-muted mb-3 fw-bold">Timeline</h6>
            <div class="timeline-detail">
                @if($transaction->paid_at)
                <div class="timeline-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <div>
                        <strong>Dibayar</strong>
                        <small class="d-block text-muted">{{ $transaction->paid_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
                @endif
                @if($transaction->rental->confirmed_at)
                <div class="timeline-item">
                    <i class="fas fa-check-circle text-info"></i>
                    <div>
                        <strong>Dikonfirmasi</strong>
                        <small class="d-block text-muted">{{ $transaction->rental->confirmed_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
                @endif
                @if($transaction->rental->picked_up_at)
                <div class="timeline-item">
                    <i class="fas fa-truck text-primary"></i>
                    <div>
                        <strong>Diambil</strong>
                        <small class="d-block text-muted">{{ $transaction->rental->picked_up_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
                @endif
                @if($transaction->rental->returned_at)
                <div class="timeline-item">
                    <i class="fas fa-check-double text-success"></i>
                    <div>
                        <strong>Dikembalikan</strong>
                        <small class="d-block text-muted">{{ $transaction->rental->returned_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
                @endif
                @if(!$transaction->paid_at && !$transaction->rental->confirmed_at && !$transaction->rental->picked_up_at && !$transaction->rental->returned_at)
                <p class="text-muted mb-0">Belum ada aktivitas</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Actions -->
        @if($transaction->isPending())
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-warning check-status-btn" data-order-id="{{ $transaction->order_id }}">
                <i class="fas fa-sync"></i> Cek Status Pembayaran
            </button>
            <button type="button" class="btn btn-danger cancel-btn" data-order-id="{{ $transaction->order_id }}">
                <i class="fas fa-times"></i> Batalkan Transaksi
            </button>
        </div>
        @endif
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
    .badge-sm {
        font-size: 0.75rem;
    }
    .offcanvas {
        max-width: 500px;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Count and update badges on load
    function updateCounts() {
        const allCount = $('.transaction-row').length;
        const confirmedCount = $('.transaction-row[data-rental-status="confirmed"]').length;
        const onRentCount = $('.transaction-row[data-rental-status="on_rent"]').length;
        const completedCount = $('.transaction-row[data-rental-status="completed"]').length;

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
            $('.transaction-row').show();
        } else if (filter === 'confirmed') {
            $('.transaction-row').hide();
            $('.transaction-row[data-rental-status="confirmed"]').show();
        } else if (filter === 'on_rent') {
            $('.transaction-row').hide();
            $('.transaction-row[data-rental-status="on_rent"]').show();
        } else if (filter === 'completed') {
            $('.transaction-row').hide();
            $('.transaction-row[data-rental-status="completed"]').show();
        }

        // Show empty message if no results
        const visibleRows = $('.transaction-row:visible').length;
        if (visibleRows === 0) {
            if ($('.no-results-row').length === 0) {
                $('tbody').append(`
                    <tr class="no-results-row">
                        <td colspan="7" class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Tidak ada data untuk filter ini</p>
                        </td>
                    </tr>
                `);
            }
        } else {
            $('.no-results-row').remove();
        }
    });

    // Check status button
    $('.check-status-btn').click(function() {
        const orderId = $(this).data('order-id');
        const btn = $(this);

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: `/payment/transaction/${orderId}/status`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    showToast('Status berhasil diperbarui', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.message || 'Gagal mengecek status', 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-sync"></i>');
                }
            },
            error: function() {
                showToast('Terjadi kesalahan', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i>');
            }
        });
    });

    // Cancel button
    $('.cancel-btn').click(function() {
        const orderId = $(this).data('order-id');
        const btn = $(this);

        confirmAction(
            'Apakah Anda yakin ingin membatalkan transaksi ini?',
            function() {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: `/payment/transaction/${orderId}/cancel`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('Transaksi berhasil dibatalkan', 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showToast(response.message || 'Gagal membatalkan transaksi', 'error');
                            btn.prop('disabled', false).html('<i class="fas fa-times"></i>');
                        }
                    },
                    error: function() {
                        showToast('Terjadi kesalahan', 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-times"></i>');
                    }
                });
            },
            {
                title: 'Konfirmasi Pembatalan',
                confirmText: 'Ya, Batalkan',
                cancelText: 'Tidak',
                confirmClass: 'btn-danger'
            }
        );
    });
});
</script>
@endpush
