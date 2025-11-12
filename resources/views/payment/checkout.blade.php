@extends('layouts.app')

@section('title', 'Checkout - Pembayaran')

@section('content')
<div class="page-header">
    <h3 class="fw-bold mb-3">Checkout</h3>
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
            <a href="{{ route('payment.page') }}">Pembayaran</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Checkout</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Detail Pesanan</h4>
            </div>
            <div class="card-body">
                <form id="checkoutForm">
                    <div id="itemsContainer">
                        <div class="item-row mb-3 border-bottom pb-3">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">Nama Item</label>
                                    <input type="text" class="form-control item-name" name="items[0][name]" placeholder="Contoh: Sewa Kamera" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Harga</label>
                                    <input type="number" class="form-control item-price" name="items[0][price]" placeholder="100000" required>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number" class="form-control item-quantity" name="items[0][quantity]" value="1" min="1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm mb-3" id="addItemBtn">
                        <i class="fas fa-plus"></i> Tambah Item
                    </button>

                    <div class="mt-4">
                        <h5>Metode Pembayaran</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="qris" id="qris" checked>
                            <label class="form-check-label" for="qris">
                                QRIS
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="bank_transfer" id="bank_transfer" checked>
                            <label class="form-check-label" for="bank_transfer">
                                Virtual Account (BCA, BNI, BRI, Mandiri)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Ringkasan Pembayaran</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Items:</span>
                    <strong id="totalItems">0</strong>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Total Pembayaran:</span>
                    <strong class="text-primary" id="totalAmount">Rp 0</strong>
                </div>
                <button type="button" class="btn btn-primary w-100" id="payButton">
                    <i class="fas fa-credit-card"></i> Bayar Sekarang
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .item-row {
        position: relative;
    }
    .remove-item-btn {
        position: absolute;
        top: 0;
        right: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
$(document).ready(function() {
    let itemIndex = 1;

    // Add item button
    $('#addItemBtn').click(function() {
        const newItem = `
            <div class="item-row mb-3 border-bottom pb-3">
                <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                    <i class="fas fa-times"></i>
                </button>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nama Item</label>
                        <input type="text" class="form-control item-name" name="items[${itemIndex}][name]" placeholder="Contoh: Sewa Kamera" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control item-price" name="items[${itemIndex}][price]" placeholder="100000" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Jumlah</label>
                        <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" value="1" min="1" required>
                    </div>
                </div>
            </div>
        `;
        $('#itemsContainer').append(newItem);
        itemIndex++;
        calculateTotal();
    });

    // Remove item button
    $(document).on('click', '.remove-item-btn', function() {
        $(this).closest('.item-row').remove();
        calculateTotal();
    });

    // Calculate total on input change
    $(document).on('input', '.item-price, .item-quantity', function() {
        calculateTotal();
    });

    // Calculate total function
    function calculateTotal() {
        let total = 0;
        let itemCount = 0;

        $('.item-row').each(function() {
            const price = parseFloat($(this).find('.item-price').val()) || 0;
            const quantity = parseInt($(this).find('.item-quantity').val()) || 0;
            total += price * quantity;
            itemCount++;
        });

        $('#totalItems').text(itemCount);
        $('#totalAmount').text(formatRupiah(total));
    }

    // Format to Rupiah
    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Pay button
    $('#payButton').click(function() {
        const items = [];
        let isValid = true;
        let grossAmount = 0;

        $('.item-row').each(function() {
            const name = $(this).find('.item-name').val();
            const price = parseFloat($(this).find('.item-price').val());
            const quantity = parseInt($(this).find('.item-quantity').val());

            if (!name || !price || !quantity) {
                isValid = false;
                return false;
            }

            items.push({
                name: name,
                price: price,
                quantity: quantity,
                id: 'ITEM-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9)
            });

            grossAmount += price * quantity;
        });

        if (!isValid || items.length === 0) {
            showToast('Mohon lengkapi semua field', 'error');
            return;
        }

        // Get enabled payments
        const enabledPayments = [];
        if ($('#qris').is(':checked')) enabledPayments.push('qris');
        if ($('#bank_transfer').is(':checked')) enabledPayments.push('bank_transfer');

        if (enabledPayments.length === 0) {
            showToast('Pilih minimal satu metode pembayaran', 'error');
            return;
        }

        // Disable button
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

        // Send to server
        $.ajax({
            url: '{{ route('payment.checkout') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                gross_amount: grossAmount,
                items: items,
                enabled_payments: enabledPayments
            },
            success: function(response) {
                if (response.success) {
                    // Open Midtrans Snap
                    snap.pay(response.snap_token, {
                        onSuccess: function(result) {
                            showToast('Pembayaran berhasil!', 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route('payment.history') }}';
                            }, 1500);
                        },
                        onPending: function(result) {
                            showToast('Menunggu pembayaran', 'info');
                            setTimeout(() => {
                                window.location.href = '{{ route('payment.history') }}';
                            }, 1500);
                        },
                        onError: function(result) {
                            showToast('Pembayaran gagal', 'error');
                            $('#payButton').prop('disabled', false).html('<i class="fas fa-credit-card"></i> Bayar Sekarang');
                        },
                        onClose: function() {
                            $('#payButton').prop('disabled', false).html('<i class="fas fa-credit-card"></i> Bayar Sekarang');
                        }
                    });
                } else {
                    showToast(response.message || 'Terjadi kesalahan', 'error');
                    $('#payButton').prop('disabled', false).html('<i class="fas fa-credit-card"></i> Bayar Sekarang');
                }
            },
            error: function(xhr) {
                showToast('Terjadi kesalahan pada server', 'error');
                $('#payButton').prop('disabled', false).html('<i class="fas fa-credit-card"></i> Bayar Sekarang');
            }
        });
    });

    // Initial calculation
    calculateTotal();
});
</script>
@endpush
