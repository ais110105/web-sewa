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
        $category = Category::create(['name' => 'Cat']);
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
