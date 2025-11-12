<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Display cart page
     */
    public function indexView(Request $request): View
    {
        $cartItems = Cart::where('user_id', $request->user()->id)
            ->with('item.category')
            ->get();

        $subtotal = 0;
        foreach ($cartItems as $cartItem) {
            // Price is per period, only multiply by quantity
            $itemSubtotal = $cartItem->item->price_per_period * $cartItem->quantity;
            $subtotal += $itemSubtotal;
        }

        // No tax
        $tax = 0;
        $total = $subtotal;

        return view('cart.index', compact('cartItems', 'subtotal', 'tax', 'total'));
    }

    /**
     * Get user's cart (API)
     */
    public function index(Request $request): JsonResponse
    {
        $cartItems = Cart::where('user_id', $request->user()->id)
            ->with('item.category')
            ->get();

        $subtotal = 0;
        $items = $cartItems->map(function ($cartItem) use (&$subtotal) {
            // Price is per period, only multiply by quantity
            $itemSubtotal = $cartItem->item->price_per_period * $cartItem->quantity;
            $subtotal += $itemSubtotal;

            return [
                'id' => $cartItem->id,
                'item' => $cartItem->item,
                'quantity' => $cartItem->quantity,
                'start_date' => $cartItem->start_date->format('Y-m-d'),
                'end_date' => $cartItem->end_date->format('Y-m-d'),
                'duration_days' => $cartItem->duration_days,
                'subtotal' => $itemSubtotal,
            ];
        });

        // No tax
        $tax = 0;
        $total = $subtotal;

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'summary' => [
                    'subtotal' => round($subtotal, 2),
                    'tax' => round($tax, 2),
                    'total' => round($total, 2),
                ],
            ],
        ]);
    }

    /**
     * Add item to cart
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if item exists and available
        $item = Item::find($request->item_id);
        if ($item->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'Item is not available',
            ], 400);
        }

        // Check if item already in cart
        $existingCart = Cart::where('user_id', $request->user()->id)
            ->where('item_id', $request->item_id)
            ->first();

        if ($existingCart) {
            return response()->json([
                'success' => false,
                'message' => 'Item already in cart. Please update the existing cart item.',
            ], 400);
        }

        // Calculate duration
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $durationDays = $startDate->diffInDays($endDate) + 1;

        // Create cart item
        $cartItem = Cart::create([
            'user_id' => $request->user()->id,
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => $cartItem->load('item'),
        ], 201);
    }

    /**
     * Update cart item
     */
    public function update(Request $request, Cart $cart): JsonResponse
    {
        // Check if cart belongs to user
        if ($cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|required|integer|min:1',
            'start_date' => 'sometimes|required|date|after_or_equal:today',
            'end_date' => 'sometimes|required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Prepare update data
        $updateData = [];

        if ($request->has('quantity')) {
            $updateData['quantity'] = $request->quantity;
        }

        if ($request->has('start_date') || $request->has('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date ?? $cart->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date ?? $cart->end_date);
            $durationDays = $startDate->diffInDays($endDate) + 1;

            $updateData['start_date'] = $startDate;
            $updateData['end_date'] = $endDate;
            $updateData['duration_days'] = $durationDays;
        }

        $cart->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'data' => $cart->load('item'),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, Cart $cart): JsonResponse
    {
        // Check if cart belongs to user
        if ($cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access',
            ], 403);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully',
        ]);
    }

    /**
     * Clear cart
     */
    public function clear(Request $request): JsonResponse
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
        ]);
    }
}
