<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Shop $shop): JsonResponse
    {
        $user = auth()->user();
        $products = Product::query()
            ->when(request()->has('searchText'), function ($query) {
                $text = request()->get('searchText');
                $query
                    ->search($text);
            }, function () use ($user, $shop) {
                if ($user->isSeller()) {
                    if ($shop->isSellerShop()) {
                        return $shop->products();
                    }
                }
            });

        return response()->json([
            'status' => trans('shop.success'),
            'data' => $products->get()
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request, Shop $shop): JsonResponse
    {
        $product = $shop->products()->create($request->validated());
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $product
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Shop $shop, Product $product): JsonResponse
    {
        $user = auth()->user();
        if ($user->isSeller()) {
            if ($shop->isSellerShop()) {
                return response()->json([
                    'status' => trans('shop.success'),
                    'data' => $product
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => trans('shop.notFound'),
                    'message' => 'You don\'t have product'
                ], Response::HTTP_NOT_FOUND);
            }
        }
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $product
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, Shop $shop, Product $product): JsonResponse
    {
        $user = auth()->user();
        if ($user->isSeller()) {
            if ($shop->isSellerShop()) {
                $product->update($request->validated());
            } else {
                return response()->json([
                    'status' => trans('shop.notFound'),
                    'data' => 'You don\'t have product'
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $product->update($request->validated());
        }
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $product
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop, Product $product): JsonResponse
    {
        $user = auth()->user();
        if ($user->isSeller()) {
            if ($shop->isSellerShop()) {
                $product->delete();
            } else {
                return response()->json([
                    'status' => trans('shop.notFound'),
                    'message' => 'You don\'t have product'
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $product->delete();
        }
        return response()->json([
            'status' => trans('shop.success'),
            'message' => 'Product successfully deleted'
        ], Response::HTTP_OK);
    }
}
