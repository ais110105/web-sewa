<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display catalog for users
     */
    public function index(Request $request): View
    {
        $this->authorize('view-catalog');

        $query = Item::where('status', 'available')->with('category');

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort by price
        if ($request->has('sort') && in_array($request->sort, ['price_asc', 'price_desc'])) {
            $query->orderBy('price_per_period', $request->sort === 'price_asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $items = $query->paginate(12);
        $categories = Category::all();

        return view('catalog.index', compact('items', 'categories'));
    }

    /**
     * Display item detail
     */
    public function show(Item $item): View
    {
        $this->authorize('view-catalog');

        $item->load('category');

        // Get related items from same category
        $relatedItems = Item::where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->where('status', 'available')
            ->limit(4)
            ->get();

        return view('catalog.show', compact('item', 'relatedItems'));
    }
}
