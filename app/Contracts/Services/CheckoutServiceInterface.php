<?php

namespace App\Contracts\Services;

use App\Models\Rental;
use App\Models\User;
use Illuminate\Support\Collection;

interface CheckoutServiceInterface
{
    public function processCheckout(User $user, array $data): Rental;

    public function calculateTotal(Collection $cartItems): array;

    public function createRentalFromCart(User $user, Collection $cartItems, array $additionalData): Rental;

    public function cancelRental(Rental $rental): bool;
}
