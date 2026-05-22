# Manajemen Pengembalian, Status Terlambat, dan Race Condition Stok

**Tanggal:** 2026-05-22
**Status:** Disetujui untuk implementasi
**Konteks:** Revisi 4 poin dari pembimbing — poin 1 (stok berkurang saat payment settle) sudah benar dan tidak diubah. Spec ini mencakup poin 2, 3, 4.

## Ringkasan

Tiga fitur saling terkait di alur sewa:

1. **Manajemen pengembalian** dengan input kondisi barang per item.
2. **Status terlambat** (overdue) tanpa denda — sekadar penanda visual + filter.
3. **Race condition stok**: aturan "siapa bayar duluan menang" dengan auto-cancel + refund untuk yang kalah.

## Poin 2 — Manajemen Pengembalian

### Perilaku saat ini
`TransactionManagementController::updateStatus` mengubah status ke `completed`, set `returned_at`, dan increment `available_stock`. Tidak ada input kondisi barang. Kolom `item_condition_return` dan `notes` di tabel `rental_items` sudah ada tapi tidak pernah diisi.

### Perilaku baru
Saat admin di halaman `management.transactions.index` menekan tombol untuk pindah dari `on_rent` ke `completed`, sistem **tidak langsung** update. Sebagai gantinya:

1. Buka modal "Konfirmasi Pengembalian" yang me-load daftar `rental_items` via endpoint baru.
2. Form per item:
   - **Kondisi** (dropdown wajib): `baik`, `rusak_ringan`, `rusak_berat`, `hilang`.
   - **Catatan** (textarea, opsional).
3. Tombol "Simpan & Selesaikan" submit ke endpoint baru.

### Aturan stok saat return

| Kondisi | `available_stock` | `stock` (total) |
|---|---|---|
| `baik` | `+= quantity` | tetap |
| `rusak_ringan` | `+= quantity` | tetap |
| `rusak_berat` | tetap | `-= quantity` |
| `hilang` | tetap | `-= quantity` |

Rasional: unit yang rusak berat / hilang tidak bisa disewakan lagi, jadi keluar dari total inventaris.

### Perubahan teknis
- Tidak ada migration baru — kolom pivot sudah tersedia.
- Endpoint baru: `POST /management/transactions/{rental}/complete-return` menerima array `items: [{rental_item_id, condition, notes}]`.
- Endpoint GET: `GET /management/transactions/{rental}/return-form-data` untuk load daftar item ke modal.
- Method baru di `Rental` model: `completeReturn(array $itemConditions): bool` yang menerima kondisi per item, melakukan update stok sesuai tabel di atas dalam satu DB transaction.
- Validasi: setiap item rental wajib punya entry kondisi.

## Poin 3 — Status Terlambat (Tanpa Denda)

### Perilaku saat ini
Status rental hanya `pending`, `confirmed`, `on_rent`, `completed`, `cancelled`. Tidak ada penanda kalau rental melewati `end_date`.

### Perilaku baru
- Tambah accessor di `Rental` model:
  - `getDaysLateAttribute(): int` — jika `status === 'on_rent'` dan `end_date < today`, return selisih hari; else `0`.
  - `getIsOverdueAttribute(): bool` — `days_late > 0`.
- Tambah scope: `scopeOverdue($query)` — `where status='on_rent' AND end_date < CURDATE()`.
- Di view `management.transactions.index`:
  - Badge merah "Terlambat X hari" muncul di kolom status untuk baris dengan `is_overdue=true`.
  - Tambah opsi "Terlambat" di filter dropdown status (selain filter status existing). Saat dipilih, query pakai `scopeOverdue()`.
- Tidak ada perhitungan denda, tidak ada notifikasi, tidak ada scheduled job.

### Perubahan teknis
- Tidak ada migration.
- Edit: `app/Models/Rental.php`, `app/Http/Controllers/TransactionManagementController.php` (handler filter), `resources/views/management/transactions/index.blade.php` (badge + opsi filter).

## Poin 4 — Race Condition Stok: Yang Bayar Duluan Menang

### Skenario masalah
Stok 2, tiga user (A, B, C) checkout & bayar hampir bersamaan. Webhook Midtrans masuk dengan urutan tak tentu. Tanpa lock, dua webhook bisa baca `available_stock=2`, dua-dua decrement, jadi `available_stock=0` padahal tiga rental sudah `confirmed`. Atau user ketiga sukses bayar tapi `decreaseStock` return `false` dan rental-nya tetap `confirmed` — user tidak dapat barang & tidak dapat refund.

### Aturan kebijakan
**Siapa bayar (settle Midtrans) duluan, dia dapat barangnya.** User yang kalah otomatis di-cancel dan di-refund.

### Implementasi
Refactor logika "settle payment" ke service tunggal supaya konsisten antara webhook dan polling `checkPaymentStatus`:

1. Method baru `PaymentService::settleRental(Rental $rental): void` yang dipanggil dari `PaymentController::webhook` dan `Api/CheckoutController::checkPaymentStatus`.
2. Dalam `DB::transaction`:
   - Lock semua `Item` terkait via `Item::whereIn('id', $itemIds)->lockForUpdate()->get()`.
   - Untuk tiap rental item, cek `available_stock >= quantity`. Jika ada satu saja yang gagal → **rollback flow sukses**, set rental ke `cancelled` dengan `notes = 'auto-cancelled: stok habis (race)'`, panggil `MidtransService::refundTransaction($orderId)`, set `payment_status = 'refunded'`.
   - Jika semua cukup → decrement semua, set rental `confirmed` + `payment_status='paid'`.
3. Refund pakai Midtrans API. Jika refund gagal (mis. metode tidak support refund instan), log error & set `payment_status='pending_refund'` supaya admin bisa tindak lanjut manual.

### Perubahan teknis
- Migration baru: tambah enum value `'refunded'` dan `'pending_refund'` ke `rentals.payment_status` (atau ubah ke string biasa kalau enum strict).
- File baru: `app/Services/PaymentService.php` dengan method `settleRental`.
- Tambah method di `MidtransService`: `refundTransaction(string $orderId, ?int $amount = null): array`.
- Edit: `PaymentController::webhook` dan `Api/CheckoutController::checkPaymentStatus` agar hanya memanggil `PaymentService::settleRental` — semua logic stok pindah ke service.
- Tampilan riwayat user (`rentals.history`): tampilkan notice "Pembayaran direfund karena stok sudah habis lebih dulu" untuk rental dengan `payment_status='refunded'`.

## Komponen Terdampak

| File | Perubahan |
|---|---|
| `database/migrations/*` | Migration baru untuk payment_status values |
| `app/Models/Rental.php` | accessor `days_late`, `is_overdue`, scope `overdue`, method `completeReturn` |
| `app/Models/Item.php` | (tidak diubah) |
| `app/Services/PaymentService.php` | **baru** — single source untuk settle |
| `app/Services/MidtransService.php` | tambah `refundTransaction` |
| `app/Http/Controllers/TransactionManagementController.php` | endpoint return form data + complete-return; filter overdue |
| `app/Http/Controllers/PaymentController.php` | webhook delegasi ke PaymentService |
| `app/Http/Controllers/Api/CheckoutController.php` | checkPaymentStatus delegasi ke PaymentService |
| `resources/views/management/transactions/index.blade.php` | badge overdue, filter, modal return |
| `resources/views/rentals/history.blade.php` | notice refund |
| `routes/web.php` | route baru untuk return form & complete |

## Out of Scope
- Notifikasi email/WhatsApp ke user untuk refund atau overdue.
- Riwayat audit log perubahan kondisi item.
- UI untuk admin re-stock barang yang rusak berat (admin bisa edit `items.stock` lewat halaman items existing).
- Denda keterlambatan (eksplisit ditolak user).
- Reservasi stok saat add-to-cart (eksplisit ditolak — stok benar di-decrement saat settle).

## Testing
- Unit test `Rental::completeReturn` dengan 4 skenario kondisi.
- Unit test accessor `days_late` & scope `overdue`.
- Integration test `PaymentService::settleRental`: 3 rental simultan untuk stok 2 → tepat 2 yang sukses, 1 di-refund.
- Manual: jalankan flow di lokal (Postgres) — checkout 2 user di browser berbeda, bayar bersamaan via sandbox Midtrans.
