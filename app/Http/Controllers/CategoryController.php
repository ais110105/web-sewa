<?php

namespace App\Http\Controllers;

use App\Contracts\Services\CategoryServiceInterface;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected CategoryServiceInterface $categoryService;

    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('view-categories');

        $categories = $this->categoryService->getPaginatedCategories(10);

        return view('categories.index', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryService->createCategory($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        $this->authorize('edit-categories');

        $category = $this->categoryService->findCategory($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->categoryService->updateCategory($id, $request->validated());

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category updated successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->authorize('delete-categories');

        try {
            $result = $this->categoryService->deleteCategory($id);

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category deleted successfully!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }
}
