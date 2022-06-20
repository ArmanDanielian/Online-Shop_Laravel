<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('parent_id', NULL)
            ->with('children')->get();
        return response()->json([
            'status' => 'Success',
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * //     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'status' => 'Success',
            'data' => $category
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCategoryRequest $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $updatedCategory = $category->update($request->validated());
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $updatedCategory
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        Category::findOrFail($id)->delete();
        return response()->json([
            'status' => trans('shop.success'),
            'message' => 'Category deleted successfully'
        ], Response::HTTP_OK);
    }
}
