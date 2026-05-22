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
        $cat = Category::create(['name' => 'X']);
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
        $cat = Category::create(['name' => 'X']);
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
        $cat = Category::create(['name' => 'X']);
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
