@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Detail Transaksi</h3>
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
            <a href="{{ route('payment.history') }}">Riwayat Transaksi</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Detail</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Informasi Transaksi</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Order ID:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $transaction->order_id }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Transaction ID:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $transaction->transaction_id ?? '-' }}
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Status:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($transaction->status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($transaction->status === 'settlement')
                            <span class="badge badge-success">Success</span>
                        @elseif($transaction->status === 'expire')
                            <span class="badge badge-danger">Expired</span>
                        @elseif($transaction->status === 'cancel')
                            <span class="badge badge-danger">Cancelled</span>
                        @elseif($transaction->status === 'deny')
                            <span class="badge badge-danger">Denied</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($transaction->status) }}</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tanggal Transaksi:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $transaction->created_at->format('d F Y H:i:s') }}
                    </div>
                </div>

                @if($transaction->paid_at)
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Tanggal Pembayaran:</strong>
                    </div>
                    <div class="col-md-8">
                        {{ $transaction->paid_at->format('d F Y H:i:s') }}
                    </div>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Total Pembayaran:</strong>
                    </div>
                    <div class="col-md-8">
                        <h4 class="text-primary">Rp {{ number_format($transaction->gross_amount, 0, ',', '.') }}</h4>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Metode Pembayaran:</strong>
                    </div>
                    <div class="col-md-8">
                        @if($transaction->payment_method === 'qris')
                            <span class="badge badge-info">QRIS</span>
                        @elseif($transaction->payment_method === 'bank_transfer')
                            <span class="badge badge-primary">Virtual Account</span>
                            @if($transaction->bank && $transaction->va_number)
                                <div class="mt-2">
                                    <strong>Bank:</strong> {{ strtoupper($transaction->bank) }}<br>
                                    <strong>VA Number:</strong> {{ $transaction->va_number }}
                                </div>
                            @endif
                        @else
                            <span class="badge badge-secondary">-</span>
                        @endif
                    </div>
                </div>

                @if($transaction->items && count($transaction->items) > 0)
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Detail Item:</strong>
                        <div class="table-responsive mt-2">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama Item</th>
                                        <th>Harga</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transaction->items as $item)
                                    <tr>
                                        <td>{{ $item['name'] ?? '-' }}</td>
                                        <td>Rp {{ number_format($item['price'] ?? 0, 0, ',', '.') }}</td>
                                        <td>{{ $item['quantity'] ?? 0 }}</td>
                                        <td>Rp {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('payment.history') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>

                    @if($transaction->isPending())
                    <button type="button" class="btn btn-warning" id="checkStatusBtn">
                        <i class="fas fa-sync"></i> Cek Status
                    </button>
                    <button type="button" class="btn btn-danger" id="cancelBtn">
                        <i class="fas fa-times"></i> Batalkan Transaksi
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @if($transaction->isPending() && $transaction->snap_url)
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Lanjutkan Pembayaran</h4>
            </div>
            <div class="card-body text-center">
                <p>Transaksi Anda masih menunggu pembayaran</p>
                <a href="{{ $transaction->snap_url }}" target="_blank" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i> Bayar Sekarang
                </a>
            </div>
        </div>
        @endif

        @if($transaction->isSettled())
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h4>Pembayaran Berhasil</h4>
                <p>Transaksi Anda telah berhasil diproses</p>
            </div>
        </div>
        @endif

        @if($transaction->isExpired() || $transaction->isCancelled())
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <i class="fas fa-times-circle fa-3x mb-3"></i>
                <h4>Transaksi {{ $transaction->isExpired() ? 'Kadaluarsa' : 'Dibatalkan' }}</h4>
                <p>Silakan buat transaksi baru</p>
                <a href="{{ route('payment.page') }}" class="btn btn-light">
                    <i class="fas fa-plus"></i> Transaksi Baru
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Check status button
    $('#checkStatusBtn').click(function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Checking...');

        $.ajax({
            url: '{{ route('payment.status', $transaction->order_id) }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    showToast('Status berhasil diperbarui', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(response.message || 'Gagal mengecek status', 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Cek Status');
                }
            },
            error: function() {
                showToast('Terjadi kesalahan', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Cek Status');
            }
        });
    });

    // Cancel button
    $('#cancelBtn').click(function() {
        const btn = $(this);

        confirmAction(
            'Apakah Anda yakin ingin membatalkan transaksi ini?',
            function() {
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cancelling...');

                $.ajax({
                    url: '{{ route('payment.cancel', $transaction->order_id) }}',
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
                            btn.prop('disabled', false).html('<i class="fas fa-times"></i> Batalkan Transaksi');
                        }
                    },
                    error: function() {
                        showToast('Terjadi kesalahan', 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-times"></i> Batalkan Transaksi');
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
