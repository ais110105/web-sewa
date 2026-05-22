# Manajemen Pengembalian, Status Terlambat, dan Race Stok — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tambah form kondisi saat pengembalian, badge & filter "Terlambat" tanpa denda, dan auto-refund untuk user yang kalah race-condition stok.

**Architecture:** Konsolidasi settlement payment ke `PaymentService` baru (single source untuk webhook + polling); tambah method `completeReturn` di `Rental` dengan aturan stok per kondisi item; tambah accessor `days_late`/`is_overdue` + scope; UI admin dapat modal kondisi item + badge merah; user dapat notice refund.

**Tech Stack:** Laravel 11, Postgres lokal (Supabase prod), Midtrans Snap, Blade + jQuery + SweetAlert2, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-05-22-rental-return-overdue-race-design.md`

---

## File Structure

**Create:**
- `app/Services/PaymentService.php` — single settle entry point dengan `lockForUpdate` + refund handler
- `tests/Unit/RentalCompleteReturnTest.php`
- `tests/Unit/RentalOverdueTest.php`
- `tests/Feature/PaymentSettlementRaceTest.php`

**Modify:**
- `app/Models/Rental.php` — `completeReturn()`, `days_late`, `is_overdue`, `scopeOverdue`
- `app/Services/MidtransService.php` — `refundTransaction()`
- `app/Http/Controllers/TransactionManagementController.php` — `returnFormData()`, `completeReturn()`, filter overdue
- `app/Http/Controllers/PaymentController.php` — webhook delegasi ke PaymentService
- `app/Http/Controllers/Api/CheckoutController.php` — checkPaymentStatus delegasi ke PaymentService
- `routes/web.php` — 2 route baru di group `transactions.`
- `resources/views/management/transactions/index.blade.php` — badge terlambat, filter, modal kondisi
- `resources/views/rentals/history.blade.php` — notice refund

---

## Task 1: Accessor `days_late` + `is_overdue` + scope `overdue` di Rental model (Poin 3)

**Files:**
- Modify: `app/Models/Rental.php`
- Test: `tests/Unit/RentalOverdueTest.php`

- [ ] **Step 1: Tulis test failing**

Buat `tests/Unit/RentalOverdueTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\Rental;
use Carbon\Carbon;
use Tests\TestCase;

class RentalOverdueTest extends TestCase
{
    public function test_days_late_returns_zero_when_not_on_rent(): void
    {
        $rental = new Rental([
            'status' => 'completed',
            'end_date' => Carbon::yesterday(),
        ]);

        $this->assertSame(0, $rental->days_late);
        $this->assertFalse($rental->is_overdue);
    }

    public function test_days_late_returns_zero_when_end_date_in_future(): void
    {
        $rental = new Rental([
            'status' => 'on_rent',
            'end_date' => Carbon::tomorrow(),
        ]);

        $this->assertSame(0, $rental->days_late);
        $this->assertFalse($rental->is_overdue);
    }

    public function test_days_late_counts_full_days_past_end_date(): void
    {
        Carbon::setTestNow('2026-05-22');

        $rental = new Rental([
            'status' => 'on_rent',
            'end_date' => Carbon::parse('2026-05-19'),
        ]);

        $this->assertSame(3, $rental->days_late);
        $this->assertTrue($rental->is_overdue);

        Carbon::setTestNow();
    }
}
```

- [ ] **Step 2: Jalankan test, verifikasi gagal**

```
./vendor/bin/phpunit tests/Unit/RentalOverdueTest.php
```
Expected: FAIL (accessor belum ada).

- [ ] **Step 3: Tambah accessor & scope di `app/Models/Rental.php`**

Tambah di bawah `markAsReturned()`:

```php
    /**
     * Days late (0 jika tidak on_rent atau belum lewat end_date).
     */
    public function getDaysLateAttribute(): int
    {
        if ($this->status !== 'on_rent' || !$this->end_date) {
            return 0;
        }

        $endDate = $this->end_date instanceof \Carbon\Carbon
            ? $this->end_date
            : \Carbon\Carbon::parse($this->end_date);

        $today = \Carbon\Carbon::today();

        if ($endDate->greaterThanOrEqualTo($today)) {
            return 0;
        }

        return (int) $endDate->diffInDays($today);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->days_late > 0;
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'on_rent')
            ->whereDate('end_date', '<', now()->toDateString());
    }
```

- [ ] **Step 4: Jalankan test, verifikasi lulus**

```
./vendor/bin/phpunit tests/Unit/RentalOverdueTest.php
```
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Models/Rental.php tests/Unit/RentalOverdueTest.php
git commit -m "feat(rental): tambah accessor days_late, is_overdue, scope overdue"
```

---

## Task 2: Filter "Terlambat" di admin transactions list (Poin 3)

**Files:**
- Modify: `app/Http/Controllers/TransactionManagementController.php:13-49`

- [ ] **Step 1: Tambah handling filter `overdue` di controller**

Di method `index()`, ubah blok filter status (sekitar baris 24-26) menjadi:

```php
        // Filter by status
        if ($request->has("status") && $request->status !== "all") {
            if ($request->status === "overdue") {
                $query->overdue();
            } else {
                $query->where("status", $request->status);
            }
        }
```

- [ ] **Step 2: Verifikasi manual**

Jalankan `php artisan serve`. Buka `/transactions?status=overdue`. Buat 1 rental dengan `status=on_rent` dan `end_date=yesterday` di tinker:

```
php artisan tinker
>>> $r = \App\Models\Rental::where('status','on_rent')->first(); $r->update(['end_date' => now()->subDays(2)]);
```

Reload `/transactions?status=overdue` → harus hanya tampil rental tersebut.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/TransactionManagementController.php
git commit -m "feat(admin): filter rental overdue di transactions list"
```

---

## Task 3: Badge "Terlambat X hari" + tab filter di view (Poin 3)

**Files:**
- Modify: `resources/views/management/transactions/index.blade.php`

- [ ] **Step 1: Tambah tab filter "Terlambat" di nav**

Cari blok nav-tabs (sekitar baris 50-60 yang berisi `data-filter="on_rent"`). Sesudah tab "Selesai", tambahkan:

```blade
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="#" data-filter="overdue">
                            Terlambat <span class="badge bg-danger ms-1" id="count-overdue">0</span>
                        </a>
                    </li>
```

- [ ] **Step 2: Tambah badge merah di kolom status untuk baris overdue**

Cari blok status rendering (sekitar baris 125-135 yang berisi `@elseif($rental->status === 'on_rent')`). Pada blok `on_rent`, ubah jadi:

```blade
                                            @elseif($rental->status === 'on_rent')
                                                <span class="badge bg-primary">Berlangsung</span>
                                                @if($rental->is_overdue)
                                                    <span class="badge bg-danger ms-1" title="end_date: {{ $rental->end_date->format('d M Y') }}">
                                                        <i class="fa fa-clock"></i> Terlambat {{ $rental->days_late }} hari
                                                    </span>
                                                @endif
```

- [ ] **Step 3: Tambah `data-overdue` ke `tr.rental-row` dan update JS counter + filter**

Cari `<tr class="rental-row"` (sekitar baris 100-an). Tambahkan attribute baru:

```blade
                                    <tr class="rental-row" data-status="{{ $rental->status }}" data-overdue="{{ $rental->is_overdue ? '1' : '0' }}">
```

(Jika attribute `data-status` sudah ada, cukup sisipkan `data-overdue="..."` setelahnya.)

Di blok JS yang menghitung count (sekitar baris 460-470), tambahkan:

```javascript
        const overdueCount = $('.rental-row[data-overdue="1"]').length;
        $('#count-overdue').text(overdueCount);
```

Di blok filter (sekitar baris 484-496), tambahkan cabang baru sebelum penutup:

```javascript
        } else if (filter === 'overdue') {
            $('.rental-row').hide();
            $('.rental-row[data-overdue="1"]').show();
```

- [ ] **Step 4: Verifikasi manual**

Jalankan `php artisan serve`. Buat 1 rental overdue (lihat Task 2 Step 2). Reload `/transactions`:
- Tab "Terlambat" muncul dengan count 1.
- Klik tab → hanya rental overdue muncul.
- Badge merah "Terlambat 2 hari" muncul di kolom status.

- [ ] **Step 5: Commit**

```bash
git add resources/views/management/transactions/index.blade.php
git commit -m "feat(admin): badge terlambat dan tab filter di transactions list"
```

---

## Task 4: Method `Rental::completeReturn()` dengan aturan stok per kondisi (Poin 2)

**Files:**
- Modify: `app/Models/Rental.php`
- Test: `tests/Unit/RentalCompleteReturnTest.php`

- [ ] **Step 1: Tulis test failing**

Buat `tests/Unit/RentalCompleteReturnTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentalCompleteReturnTest extends TestCase
{
    use RefreshDatabase;

    private function makeRentalWithItem(int $stock, int $available, int $qty): array
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'Tenda',
            'description' => 'x',
            'status' => 'available',
            'price_per_period' => 10000,
            'stock' => $stock,
            'available_stock' => $available,
        ]);
        $rental = Rental::create([
            'rental_code' => 'RENT-TEST-1',
            'user_id' => $user->id,
            'start_date' => now(),
            'end_date' => now()->addDays(2),
            'duration_days' => 3,
            'subtotal' => 30000,
            'tax' => 0,
            'total_price' => 30000,
            'status' => 'on_rent',
            'payment_status' => 'paid',
        ]);
        $ri = RentalItem::create([
            'rental_id' => $rental->id,
            'item_id' => $item->id,
            'quantity' => $qty,
            'price_per_day' => 10000,
            'subtotal' => 30000,
        ]);
        return [$rental, $item, $ri];
    }

    public function test_baik_increments_available_stock_only(): void
    {
        [$rental, $item, $ri] = $this->makeRentalWithItem(5, 3, 2);

        $rental->completeReturn([
            ['rental_item_id' => $ri->id, 'condition' => 'baik', 'notes' => null],
        ]);

        $item->refresh();
        $this->assertSame(5, $item->available_stock);
        $this->assertSame(5, $item->stock);
        $this->assertSame('completed', $rental->fresh()->status);
        $this->assertNotNull($rental->fresh()->returned_at);
        $this->assertSame('baik', $ri->fresh()->item_condition_return);
    }

    public function test_rusak_ringan_behaves_like_baik(): void
    {
        [$rental, $item, $ri] = $this->makeRentalWithItem(5, 3, 2);

        $rental->completeReturn([
            ['rental_item_id' => $ri->id, 'condition' => 'rusak_ringan', 'notes' => 'gores kecil'],
        ]);

        $item->refresh();
        $this->assertSame(5, $item->available_stock);
        $this->assertSame(5, $item->stock);
        $this->assertSame('gores kecil', $ri->fresh()->notes);
    }

    public function test_rusak_berat_decrements_total_stock_not_available(): void
    {
        [$rental, $item, $ri] = $this->makeRentalWithItem(5, 3, 2);

        $rental->completeReturn([
            ['rental_item_id' => $ri->id, 'condition' => 'rusak_berat', 'notes' => null],
        ]);

        $item->refresh();
        $this->assertSame(3, $item->available_stock);
        $this->assertSame(3, $item->stock);
    }

    public function test_hilang_decrements_total_stock_not_available(): void
    {
        [$rental, $item, $ri] = $this->makeRentalWithItem(5, 3, 2);

        $rental->completeReturn([
            ['rental_item_id' => $ri->id, 'condition' => 'hilang', 'notes' => null],
        ]);

        $item->refresh();
        $this->assertSame(3, $item->available_stock);
        $this->assertSame(3, $item->stock);
    }
}
```

- [ ] **Step 2: Jalankan test, verifikasi gagal**

```
./vendor/bin/phpunit tests/Unit/RentalCompleteReturnTest.php
```
Expected: FAIL ("Call to undefined method completeReturn").

- [ ] **Step 3: Tambah method `completeReturn` di `app/Models/Rental.php`**

Tambah di bawah `markAsReturned()`:

```php
    /**
     * Selesaikan rental dengan kondisi per item.
     * $itemConditions: [['rental_item_id' => int, 'condition' => string, 'notes' => ?string], ...]
     * condition: 'baik' | 'rusak_ringan' | 'rusak_berat' | 'hilang'
     */
    public function completeReturn(array $itemConditions): bool
    {
        if (!in_array($this->status, ['on_rent', 'confirmed'], true)) {
            return false;
        }

        $byId = collect($itemConditions)->keyBy('rental_item_id');

        return \Illuminate\Support\Facades\DB::transaction(function () use ($byId) {
            foreach ($this->rentalItems()->with('item')->get() as $rentalItem) {
                $entry = $byId->get($rentalItem->id);
                if (!$entry) {
                    throw new \RuntimeException("Kondisi untuk rental_item {$rentalItem->id} tidak diberikan");
                }
                $condition = $entry['condition'];
                $notes = $entry['notes'] ?? null;

                $rentalItem->update([
                    'item_condition_return' => $condition,
                    'notes' => $notes,
                ]);

                $item = $rentalItem->item;
                if (in_array($condition, ['baik', 'rusak_ringan'], true)) {
                    $item->increment('available_stock', $rentalItem->quantity);
                } elseif (in_array($condition, ['rusak_berat', 'hilang'], true)) {
                    $item->decrement('stock', $rentalItem->quantity);
                } else {
                    throw new \InvalidArgumentException("Kondisi tidak valid: {$condition}");
                }
            }

            $this->update([
                'status' => 'completed',
                'returned_at' => now(),
            ]);

            return true;
        });
    }
```

- [ ] **Step 4: Jalankan test, verifikasi lulus**

```
./vendor/bin/phpunit tests/Unit/RentalCompleteReturnTest.php
```
Expected: 4 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Models/Rental.php tests/Unit/RentalCompleteReturnTest.php
git commit -m "feat(rental): completeReturn dengan aturan stok per kondisi item"
```

---

## Task 5: Endpoint admin untuk return form data & complete return (Poin 2)

**Files:**
- Modify: `app/Http/Controllers/TransactionManagementController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Tambah method di controller**

Di `TransactionManagementController.php`, tambah dua method baru setelah `updateStatus`:

```php
    /**
     * GET data untuk modal form pengembalian.
     */
    public function returnFormData(\App\Models\Rental $rental)
    {
        $rental->load('rentalItems.item');

        return response()->json([
            'success' => true,
            'data' => [
                'rental_code' => $rental->rental_code,
                'items' => $rental->rentalItems->map(function ($ri) {
                    return [
                        'rental_item_id' => $ri->id,
                        'item_name' => $ri->item->name,
                        'quantity' => $ri->quantity,
                    ];
                }),
            ],
        ]);
    }

    /**
     * POST: simpan kondisi tiap item + ubah ke completed.
     */
    public function completeReturn(\Illuminate\Http\Request $request, \App\Models\Rental $rental)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.rental_item_id' => 'required|integer|exists:rental_items,id',
            'items.*.condition' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            if (!$rental->completeReturn($validated['items'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental tidak dapat diselesaikan dari status saat ini',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengembalian berhasil dicatat',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat pengembalian: ' . $e->getMessage(),
            ], 500);
        }
    }
```

- [ ] **Step 2: Tambah route di `routes/web.php`**

Di group `transactions.` (sekitar baris 178-196), tambah setelah route `update-status`:

```php
            Route::get("/{rental}/return-form-data", [
                \App\Http\Controllers\TransactionManagementController::class,
                "returnFormData",
            ])->name("return.form-data");
            Route::post("/{rental}/complete-return", [
                \App\Http\Controllers\TransactionManagementController::class,
                "completeReturn",
            ])->name("complete.return");
```

- [ ] **Step 3: Verifikasi route terdaftar**

```
php artisan route:list --path=transactions
```
Expected: 2 route baru `return.form-data` dan `complete.return` muncul.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/TransactionManagementController.php routes/web.php
git commit -m "feat(admin): endpoint return-form-data dan complete-return"
```

---

## Task 6: Modal kondisi item di view admin (Poin 2)

**Files:**
- Modify: `resources/views/management/transactions/index.blade.php`

- [ ] **Step 1: Tambah HTML modal di akhir body (sebelum `@endsection` atau penutup section)**

Cari penutup section utama (biasanya sebelum `@push('scripts')`). Tambahkan:

```blade
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
```

- [ ] **Step 2: Ubah handler `next-step-btn` agar buka modal kalau next-status = completed**

Cari handler `.next-step-btn` (sekitar baris 539). Ubah body-nya menjadi:

```javascript
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
```

- [ ] **Step 3: Tambah fungsi `openReturnModal` + handler submit form**

Di akhir blok `<script>` (sebelum `});` penutup document ready), tambah:

```javascript
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
```

- [ ] **Step 4: Verifikasi manual**

`php artisan serve`. Buat rental `status=on_rent` dengan ≥2 items. Klik tombol "Tandai Dikembalikan" → modal muncul dengan list item & dropdown kondisi. Pilih `rusak_berat` untuk salah satu, submit. Cek DB: `rental_items.item_condition_return` terisi, `items.stock` berkurang, `items.available_stock` tetap.

- [ ] **Step 5: Commit**

```bash
git add resources/views/management/transactions/index.blade.php
git commit -m "feat(admin): modal kondisi item saat konfirmasi pengembalian"
```

---

## Task 7: Method `MidtransService::refundTransaction` (Poin 4)

**Files:**
- Modify: `app/Services/MidtransService.php`

- [ ] **Step 1: Tambah method refundTransaction**

Tambah method baru setelah `cancelTransaction`:

```php
    /**
     * Refund transaksi via Midtrans API.
     * Untuk metode yang tidak support instant refund (mis. bank transfer manual),
     * API akan return error; caller wajib catch & tindak lanjut.
     */
    public function refundTransaction(string $orderId, ?int $amount = null, string $reason = 'Auto refund'): array
    {
        try {
            $params = ['reason' => $reason];
            if ($amount !== null) {
                $params['amount'] = $amount;
            }

            $response = MidtransTransaction::refund($orderId, $params);

            $transaction = Transaction::where('order_id', $orderId)->first();
            if ($transaction) {
                $transaction->update(['status' => 'refund']);
            }

            return is_object($response) ? json_decode(json_encode($response), true) : (array) $response;
        } catch (\Exception $e) {
            \Log::error('Midtrans refund failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
```

- [ ] **Step 2: Smoke check syntax**

```
php -l app/Services/MidtransService.php
```
Expected: "No syntax errors detected".

- [ ] **Step 3: Commit**

```bash
git add app/Services/MidtransService.php
git commit -m "feat(midtrans): refundTransaction untuk auto-refund race loser"
```

---

## Task 8: `PaymentService::settleRental` dengan lock + race handling (Poin 4)

**Files:**
- Create: `app/Services/PaymentService.php`
- Test: `tests/Feature/PaymentSettlementRaceTest.php`

- [ ] **Step 1: Tulis test failing**

Buat `tests/Feature/PaymentSettlementRaceTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PaymentSettlementRaceTest extends TestCase
{
    use RefreshDatabase;

    private function makeRentalForItem(Item $item, int $qty): Rental
    {
        $user = User::factory()->create();
        $tx = Transaction::create([
            'user_id' => $user->id,
            'order_id' => 'ORDER-' . uniqid(),
            'gross_amount' => 10000 * $qty,
            'items' => [],
            'status' => 'pending',
        ]);
        $rental = Rental::create([
            'rental_code' => 'RENT-' . uniqid(),
            'user_id' => $user->id,
            'transaction_id' => $tx->id,
            'start_date' => now(),
            'end_date' => now()->addDay(),
            'duration_days' => 2,
            'subtotal' => 10000 * $qty,
            'tax' => 0,
            'total_price' => 10000 * $qty,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        RentalItem::create([
            'rental_id' => $rental->id,
            'item_id' => $item->id,
            'quantity' => $qty,
            'price_per_day' => 10000,
            'subtotal' => 10000 * $qty,
        ]);
        return $rental;
    }

    public function test_settle_when_stock_sufficient_marks_paid_and_decreases_stock(): void
    {
        $cat = Category::create(['name' => 'X', 'slug' => 'x']);
        $item = Item::create([
            'category_id' => $cat->id, 'name' => 'A', 'description' => 'x',
            'status' => 'available', 'price_per_period' => 10000,
            'stock' => 2, 'available_stock' => 2,
        ]);
        $rental = $this->makeRentalForItem($item, 1);

        $midtrans = Mockery::mock(MidtransService::class);
        $svc = new PaymentService($midtrans);

        $svc->settleRental($rental);

        $rental->refresh();
        $item->refresh();
        $this->assertSame('paid', $rental->payment_status);
        $this->assertSame('confirmed', $rental->status);
        $this->assertSame(1, $item->available_stock);
    }

    public function test_settle_when_stock_exhausted_cancels_and_refunds(): void
    {
        $cat = Category::create(['name' => 'X', 'slug' => 'x']);
        $item = Item::create([
            'category_id' => $cat->id, 'name' => 'A', 'description' => 'x',
            'status' => 'available', 'price_per_period' => 10000,
            'stock' => 1, 'available_stock' => 0,
        ]);
        $rental = $this->makeRentalForItem($item, 1);

        $midtrans = Mockery::mock(MidtransService::class);
        $midtrans->shouldReceive('refundTransaction')
            ->once()
            ->with($rental->transaction->order_id, Mockery::any(), Mockery::any())
            ->andReturn(['status_code' => '200']);

        $svc = new PaymentService($midtrans);
        $svc->settleRental($rental);

        $rental->refresh();
        $item->refresh();
        $this->assertSame('cancelled', $rental->status);
        $this->assertSame('refunded', $rental->payment_status);
        $this->assertStringContainsString('stok habis', $rental->notes);
        $this->assertSame(0, $item->available_stock);
    }

    public function test_settle_refund_failure_marks_pending_refund(): void
    {
        $cat = Category::create(['name' => 'X', 'slug' => 'x']);
        $item = Item::create([
            'category_id' => $cat->id, 'name' => 'A', 'description' => 'x',
            'status' => 'available', 'price_per_period' => 10000,
            'stock' => 1, 'available_stock' => 0,
        ]);
        $rental = $this->makeRentalForItem($item, 1);

        $midtrans = Mockery::mock(MidtransService::class);
        $midtrans->shouldReceive('refundTransaction')
            ->andThrow(new \Exception('refund not supported'));

        $svc = new PaymentService($midtrans);
        $svc->settleRental($rental);

        $rental->refresh();
        $this->assertSame('cancelled', $rental->status);
        $this->assertSame('pending_refund', $rental->payment_status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
```

- [ ] **Step 2: Jalankan test, verifikasi gagal**

```
./vendor/bin/phpunit tests/Feature/PaymentSettlementRaceTest.php
```
Expected: FAIL ("Class PaymentService not found").

- [ ] **Step 3: Buat `app/Services/PaymentService.php`**

```php
<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Rental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(protected MidtransService $midtransService) {}

    /**
     * Settle rental setelah payment sukses.
     * Aturan: yang lebih dulu masuk lock + stok cukup → confirmed.
     * Yang stoknya habis → cancelled + refund.
     */
    public function settleRental(Rental $rental): void
    {
        $rental->loadMissing('rentalItems', 'transaction');

        // Idempotency: kalau sudah paid/refunded, skip.
        if (in_array($rental->payment_status, ['paid', 'refunded', 'pending_refund'], true)) {
            return;
        }

        $itemIds = $rental->rentalItems->pluck('item_id')->all();

        $stockOk = DB::transaction(function () use ($rental, $itemIds) {
            $items = Item::whereIn('id', $itemIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($rental->rentalItems as $ri) {
                $item = $items[$ri->item_id] ?? null;
                if (!$item || $item->available_stock < $ri->quantity) {
                    return false;
                }
            }

            foreach ($rental->rentalItems as $ri) {
                $items[$ri->item_id]->decrement('available_stock', $ri->quantity);
            }

            $rental->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            if ($rental->transaction) {
                $rental->transaction->update(['paid_at' => now()]);
            }

            return true;
        });

        if ($stockOk) {
            return;
        }

        $this->cancelAndRefund($rental);
    }

    protected function cancelAndRefund(Rental $rental): void
    {
        $orderId = $rental->transaction?->order_id;
        $reasonNote = 'auto-cancelled: stok habis (race condition)';

        try {
            if ($orderId) {
                $this->midtransService->refundTransaction(
                    $orderId,
                    (int) $rental->total_price,
                    $reasonNote,
                );
            }

            $rental->update([
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'notes' => trim(($rental->notes ?? '') . "\n" . $reasonNote),
            ]);
        } catch (\Exception $e) {
            Log::error('Auto-refund failed; marked pending_refund', [
                'rental_id' => $rental->id,
                'error' => $e->getMessage(),
            ]);
            $rental->update([
                'status' => 'cancelled',
                'payment_status' => 'pending_refund',
                'notes' => trim(($rental->notes ?? '') . "\n" . $reasonNote . " (refund manual: {$e->getMessage()})"),
            ]);
        }
    }
}
```

- [ ] **Step 4: Jalankan test, verifikasi lulus**

```
./vendor/bin/phpunit tests/Feature/PaymentSettlementRaceTest.php
```
Expected: 3 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/PaymentService.php tests/Feature/PaymentSettlementRaceTest.php
git commit -m "feat(payment): PaymentService settleRental dengan lock + auto-refund"
```

---

## Task 9: Delegasi webhook & polling ke `PaymentService` (Poin 4)

**Files:**
- Modify: `app/Http/Controllers/PaymentController.php:139-186`
- Modify: `app/Http/Controllers/Api/CheckoutController.php:243-256`

- [ ] **Step 1: Inject `PaymentService` di `PaymentController`**

Ubah constructor:

```php
    protected $midtransService;
    protected $paymentService;

    public function __construct(MidtransService $midtransService, \App\Services\PaymentService $paymentService)
    {
        $this->midtransService = $midtransService;
        $this->paymentService = $paymentService;
    }
```

- [ ] **Step 2: Refactor `webhook()` agar pakai PaymentService**

Ganti seluruh body method `webhook` menjadi:

```php
    public function webhook(Request $request)
    {
        try {
            $transaction = $this->midtransService->handleNotification($request->all());

            if ($transaction->status === 'settlement') {
                $rental = Rental::where('transaction_id', $transaction->id)->first();
                if ($rental) {
                    $this->paymentService->settleRental($rental);
                    Log::info("Rental {$rental->rental_code} settled via webhook");
                }
            }

            if (in_array($transaction->status, ['expire', 'cancel', 'deny'])) {
                $rental = Rental::where('transaction_id', $transaction->id)->first();
                if ($rental && $rental->status === 'pending') {
                    $rental->update(['status' => 'cancelled']);
                    Log::info("Rental {$rental->rental_code} cancelled due to payment {$transaction->status}");
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
```

- [ ] **Step 3: Inject & refactor `checkPaymentStatus` di `Api/CheckoutController`**

Tambah property + constructor injection:

```php
    protected \App\Services\PaymentService $paymentService;

    public function __construct(
        CheckoutServiceInterface $checkoutService,
        MidtransService $midtransService,
        \App\Services\PaymentService $paymentService
    ) {
        $this->checkoutService = $checkoutService;
        $this->midtransService = $midtransService;
        $this->paymentService = $paymentService;
    }
```

Di `checkPaymentStatus()`, ganti blok `if ($newStatus === 'settlement')` (baris ~243-264) menjadi:

```php
            if ($newStatus === 'settlement') {
                $this->paymentService->settleRental($rental);
                $rental->refresh();

                return response()->json([
                    'success' => true,
                    'message' => $rental->payment_status === 'paid'
                        ? 'Payment confirmed! Rental status updated.'
                        : 'Pembayaran diterima tapi stok sudah habis — refund diproses.',
                    'data' => [
                        'payment_status' => $rental->payment_status,
                        'rental_status' => $rental->status,
                    ],
                ]);
            } elseif (in_array($newStatus, ['expire', 'cancel', 'deny'])) {
                $rental->update(['status' => 'cancelled']);
                return response()->json([
                    'success' => true,
                    'message' => 'Payment ' . $newStatus,
                    'data' => [
                        'payment_status' => $rental->payment_status,
                        'rental_status' => 'cancelled',
                    ],
                ]);
            }
```

- [ ] **Step 4: Smoke check**

```
php artisan route:cache && php artisan route:clear
./vendor/bin/phpunit tests/Feature/PaymentSettlementRaceTest.php
```
Expected: tests masih PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PaymentController.php app/Http/Controllers/Api/CheckoutController.php
git commit -m "refactor(payment): delegasi webhook & polling ke PaymentService"
```

---

## Task 10: Notice refund di halaman riwayat user (Poin 4)

**Files:**
- Modify: `resources/views/rentals/history.blade.php`

- [ ] **Step 1: Inspeksi struktur view**

```
grep -n "payment_status\|status\|@foreach" resources/views/rentals/history.blade.php | head -20
```

- [ ] **Step 2: Tambah notice untuk payment_status refunded / pending_refund**

Di dalam loop `@foreach($rentals as $rental)`, pada bagian status/badge rental (cari render `$rental->payment_status` atau `$rental->status`), tambahkan blok ini sesudah render status utama:

```blade
                @if(in_array($rental->payment_status, ['refunded', 'pending_refund']))
                    <div class="alert alert-warning small mt-2 mb-0 py-2">
                        <i class="fa fa-info-circle"></i>
                        @if($rental->payment_status === 'refunded')
                            Pembayaran Anda <strong>direfund otomatis</strong> karena stok sudah habis lebih dulu.
                        @else
                            Pembayaran Anda akan <strong>direfund manual</strong> oleh admin karena stok sudah habis. Mohon menunggu.
                        @endif
                    </div>
                @endif
```

Jika file menggunakan struktur card, sisipkan blok di dalam card body. Jika tidak yakin lokasi tepat, sisipkan tepat sebelum tombol aksi rental (mis. tombol "Bayar Lagi" / "Detail").

- [ ] **Step 3: Verifikasi manual**

Update salah satu rental test via tinker:

```
php artisan tinker
>>> \App\Models\Rental::first()->update(['payment_status' => 'refunded']);
```

Buka `/rental` (halaman history) sebagai user pemilik rental tersebut → notice kuning muncul.

- [ ] **Step 4: Commit**

```bash
git add resources/views/rentals/history.blade.php
git commit -m "feat(user): notice refund di halaman riwayat rental"
```

---

## Task 11: Verifikasi end-to-end & jalankan full test suite

**Files:** —

- [ ] **Step 1: Jalankan seluruh test**

```
./vendor/bin/phpunit
```
Expected: semua PASS (atau hanya test pre-existing yang gagal — bukan dari task ini).

- [ ] **Step 2: Manual smoke test alur lengkap**

`php artisan serve`. Lakukan dalam urutan:

1. Sebagai user A: checkout 1 item (stok=1), bayar di sandbox Midtrans → status berubah ke `confirmed`, `available_stock=0`.
2. Sebagai user B (browser kedua): coba checkout item sama. Karena `available_stock=0`, validasi checkout akan tolak (existing behavior). Untuk simulasi race, set ulang `available_stock=1` via tinker setelah user B masuk halaman pembayaran, lalu bayar dari kedua tab dengan cepat — salah satu akan dapat status `refunded`.
3. Sebagai admin: buka `/transactions`. Tab "Terlambat" muncul. Set rental on_rent dengan end_date kemarin → badge merah muncul.
4. Sebagai admin: klik "Tandai Dikembalikan" → modal muncul, isi kondisi `rusak_berat` untuk salah satu item → submit. Cek items.stock turun, available_stock tetap.
5. Sebagai user dengan rental refunded: buka `/rental` → notice kuning muncul.

- [ ] **Step 3: Final commit (jika ada perbaikan kecil)**

```bash
git status
# jika ada perubahan tambahan dari smoke test (bug minor), commit:
git add -A && git commit -m "fix: penyesuaian minor setelah smoke test e2e"
```

---

## Self-Review Notes

- **Spec coverage:** Poin 2 → Task 4-6. Poin 3 → Task 1-3. Poin 4 → Task 7-10. Verifikasi e2e → Task 11. ✅
- **Tidak ada migration baru** karena `rental_items` sudah punya `item_condition_return` + `notes`, dan `rentals.payment_status` sudah `string` (bisa terima value `refunded`/`pending_refund` tanpa schema change).
- **Idempotency PaymentService**: webhook bisa double-fire; `settleRental` skip kalau status sudah final.
- **YAGNI:** Tidak ada notifikasi email, audit log, atau scheduled job — sesuai spec.
