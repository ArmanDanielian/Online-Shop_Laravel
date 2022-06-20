<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderProductImageRequest;
use App\Http\Requests\StoreProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductImageRequest $request, Product $product): JsonResponse
    {
        if ($product->shop->isSellerShop()) {
            $file = $request->validated()['path'];
            $file->store('public/Uploads');
            $name = $file->getClientOriginalName();
            $validated = $request->validated();
            $validated['name'] = $name;
            $image = $product->images()->create($validated);

            return response()->json([
                'message' => 'File uploaded',
                'data' => $image
            ], 200);
        }
        return response()->json([
            'message' => 'You don\'t have product'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product, ProductImage $image): JsonResponse
    {
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $image
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Сhange the order of the images
     *
     * @param ReorderProductImageRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function reorder(ReorderProductImageRequest $request, Product $product): JsonResponse
    {
        $orders = $request->validated()['orders'];
        $ids = array_column($orders, 'id');
        $image_ids = ProductImage::whereIn('id', $ids)->get()->pluck('product_id')->toArray();
        if (count(array_unique($image_ids)) === 1) { # All belong to the same product
            $sellerProducts = auth()->user()->products;
            $sellerProductNames = $sellerProducts->pluck('name')->toArray();
            if (in_array($product->name, $sellerProductNames)) {
                foreach ($orders as $item) {
                    ProductImage::findOrFail($item['id'])->update([
                        'order' => $item['order']
                    ]);
                }
                return response()->json([
                    'status' => trans('shop.success'),
                    'message' => 'The order of images has changed successfully'
                ], Response::HTTP_OK);
            }
            else {
                return response()->json([
                    'status' => trans('shop.notFound')
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            return response()->json([
                'status' => trans('shop.notFound'),
                'message' => 'You can\'t reorder images of different products at the same time'
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
