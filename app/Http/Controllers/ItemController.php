<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ItemServiceInterface;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ItemController extends Controller
{
    protected ItemServiceInterface $itemService;

    public function __construct(ItemServiceInterface $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('view-items');

        $items = $this->itemService->getPaginatedItems(10);
        $categories = Category::all();

        return view('items.index', compact('items', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        try {
            $item = $this->itemService->createItem($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully!',
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $this->authorize('edit-items');

        $item = $this->itemService->findItem($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'description' => $item->description,
                'price_per_period' => $item->price_per_period,
                'status' => $item->status,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateItemRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->itemService->updateItem($id, $request->validated());

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item updated successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update item'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete-items');

        try {
            $result = $this->itemService->deleteItem($id);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item deleted successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage()
            ], 500);
        }
    }
}
