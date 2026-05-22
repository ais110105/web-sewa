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
