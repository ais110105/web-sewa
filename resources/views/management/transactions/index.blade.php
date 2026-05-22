@extends('layouts.app')

@section('title', 'Kelola Transaksi - Tirta Kesuma')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Kelola Transaksi</h3>
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
            <a href="#">Transaksi</a>
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
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="#" data-filter="overdue">
                            Terlambat <span class="badge bg-danger ms-1" id="count-overdue">0</span>
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
                                        <th width="12%">Pelanggan</th>
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
                                    <tr class="rental-row" data-status="{{ $rental->status }}" data-overdue="{{ $rental->is_overdue ? '1' : '0' }}" data-payment="{{ $rental->payment_status }}">
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
                                                    <i class="fas fa-clock"></i> Menunggu
                                                </span>
                                            @elseif($rental->status === 'confirmed')
                                                <span class="badge badge-info">
                                                    <i class="fas fa-check"></i> Dikonfirmasi
                                                </span>
                                            @elseif($rental->status === 'on_rent')
                                                <span class="badge bg-primary">Berlangsung</span>
                                                @if($rental->is_overdue)
                                                    <span class="badge bg-danger ms-1" title="end_date: {{ $rental->end_date->format('d M Y') }}">
                                                        <i class="fa fa-clock"></i> Terlambat {{ $rental->days_late }} hari
                                                    </span>
                                                @endif
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
                                                    <i class="fas fa-undo"></i> Dikembalikan
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
                                                @php
                                                    $nextMap = [
                                                        'pending'   => ['status' => 'confirmed', 'label' => 'Konfirmasi',         'icon' => 'fa-check',        'class' => 'btn-info'],
                                                        'confirmed' => ['status' => 'on_rent',   'label' => 'Tandai Diambil',     'icon' => 'fa-truck',        'class' => 'btn-primary'],
                                                        'on_rent'   => ['status' => 'completed', 'label' => 'Tandai Dikembalikan','icon' => 'fa-check-double', 'class' => 'btn-success'],
                                                    ];
                                                    $next = $nextMap[$rental->status] ?? null;
                                                @endphp
                                                @if($next)
                                                    <button type="button" class="btn {{ $next['class'] }} btn-sm mb-1 next-step-btn"
                                                        data-rental-id="{{ $rental->id }}"
                                                        data-next-status="{{ $next['status'] }}"
                                                        data-next-label="{{ $next['label'] }}"
                                                        title="{{ $next['label'] }}">
                                                        <i class="fas {{ $next['icon'] }}"></i> {{ $next['label'] }}
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm mb-1" disabled>
                                                        <i class="fas fa-flag-checkered"></i>
                                                        {{ $rental->status === 'cancelled' ? 'Dibatalkan' : 'Selesai' }}
                                                    </button>
                                                @endif
                                                @if(in_array($rental->status, ['pending', 'confirmed']))
                                                    <button type="button" class="btn btn-outline-danger btn-sm cancel-rental-btn"
                                                        data-rental-id="{{ $rental->id }}"
                                                        title="Batalkan">
                                                        <i class="fas fa-times"></i> Batalkan
                                                    </button>
                                                @endif
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
            <h6 class="text-muted mb-3 fw-bold">Informasi Pelanggan</h6>
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
                    <td class="text-muted">Telepon</td>
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
                    <td class="text-muted">Pajak</td>
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
                            <span class="badge badge-secondary">Dikembalikan</span>
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

<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pengembalian — <span id="returnRentalCode"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="returnForm">
                <div class="modal-body">
                    <div id="returnItemsContainer">
                        <!-- diisi via JS -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="returnSubmitBtn">
                        <i class="fa fa-check-double"></i> Simpan & Selesaikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
$(document).ready(function() {
    // Count and update badges on load
    function updateCounts() {
        const allCount = $('.rental-row').length;
        const confirmedCount = $('.rental-row[data-status="confirmed"]').length;
        const onRentCount = $('.rental-row[data-status="on_rent"]').length;
        const completedCount = $('.rental-row[data-status="completed"]').length;

        const overdueCount = $('.rental-row[data-overdue="1"]').length;

        $('#count-all').text(allCount);
        $('#count-confirmed').text(confirmedCount);
        $('#count-on_rent').text(onRentCount);
        $('#count-completed').text(completedCount);
        $('#count-overdue').text(overdueCount);
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
        } else if (filter === 'overdue') {
            $('.rental-row').hide();
            $('.rental-row[data-overdue="1"]').show();
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

    function postStatus(rentalId, newStatus, successMsg) {
        $.ajax({
            url: `/transactions/${rentalId}/update-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    showToast(successMsg, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(response.message || 'Gagal update status', 'error');
                }
            },
            error: function() {
                showToast('Terjadi kesalahan', 'error');
            }
        });
    }

    // Sequential next-step button
    $(document).on('click', '.next-step-btn', function() {
        const rentalId = $(this).data('rental-id');
        const nextStatus = $(this).data('next-status');
        const nextLabel = $(this).data('next-label');

        if (nextStatus === 'completed') {
            openReturnModal(rentalId);
            return;
        }

        Swal.fire({
            title: nextLabel + '?',
            text: 'Lanjutkan rental ke tahap "' + nextLabel + '"?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
        }).then((result) => {
            if (result.isConfirmed) {
                postStatus(rentalId, nextStatus, 'Status diperbarui ke ' + nextLabel);
            }
        });
    });

    // Cancel rental
    $(document).on('click', '.cancel-rental-btn', function() {
        const rentalId = $(this).data('rental-id');

        Swal.fire({
            title: 'Batalkan Rental?',
            text: 'Rental yang dibatalkan tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                postStatus(rentalId, 'cancelled', 'Rental dibatalkan');
            }
        });
    });

    function openReturnModal(rentalId) {
        $.get(`/transactions/${rentalId}/return-form-data`, function(resp) {
            if (!resp.success) {
                showToast('Gagal memuat data', 'error');
                return;
            }
            $('#returnRentalCode').text(resp.data.rental_code);
            const container = $('#returnItemsContainer');
            container.empty();
            resp.data.items.forEach(function(it, idx) {
                container.append(`
                    <div class="border rounded p-3 mb-3" data-rental-item-id="${it.rental_item_id}">
                        <h6 class="mb-2">${it.item_name} <span class="text-muted">× ${it.quantity}</span></h6>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Kondisi <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm item-condition" required>
                                <option value="">-- Pilih kondisi --</option>
                                <option value="baik">Baik</option>
                                <option value="rusak_ringan">Rusak Ringan</option>
                                <option value="rusak_berat">Rusak Berat</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small mb-1">Catatan (opsional)</label>
                            <textarea class="form-control form-control-sm item-notes" rows="2" maxlength="500"></textarea>
                        </div>
                    </div>
                `);
            });
            $('#returnForm').data('rental-id', rentalId);
            new bootstrap.Modal(document.getElementById('returnModal')).show();
        }).fail(function() {
            showToast('Terjadi kesalahan', 'error');
        });
    }

    $('#returnForm').on('submit', function(e) {
        e.preventDefault();
        const rentalId = $(this).data('rental-id');
        const items = [];
        let valid = true;
        $('#returnItemsContainer > div').each(function() {
            const condition = $(this).find('.item-condition').val();
            if (!condition) { valid = false; return; }
            items.push({
                rental_item_id: parseInt($(this).data('rental-item-id'), 10),
                condition: condition,
                notes: $(this).find('.item-notes').val() || null,
            });
        });
        if (!valid) {
            showToast('Semua kondisi item wajib dipilih', 'error');
            return;
        }
        $('#returnSubmitBtn').prop('disabled', true);
        $.ajax({
            url: `/transactions/${rentalId}/complete-return`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', items: items },
            success: function(resp) {
                if (resp.success) {
                    showToast(resp.message, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showToast(resp.message || 'Gagal', 'error');
                    $('#returnSubmitBtn').prop('disabled', false);
                }
            },
            error: function(xhr) {
                showToast(xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                $('#returnSubmitBtn').prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
