<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use App\Models\User;
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
    public function store(Request $request): JsonResponse
    {
        if (!$request->hasFile('path')) {
            return response()->json(['upload_file_not_found'], 400);
        }

        $allowedfileExtension = ['jpg', 'png'];
        $file = $request->file('path');

        $extension = $file->getClientOriginalExtension();

        $check = in_array($extension, $allowedfileExtension);

        if ($check) {
            $mediaFile = $request->path;

            $path = $mediaFile->store('public/Uploads');
            $name = $mediaFile->getClientOriginalName();

            //store image file into directory and db
            $save = new ProductImage();
            $save->name = $name;
            $save->path = $path;
            $save->product_id = $request->product_id;
            $save->default = $request->default;
            $save->order = $request->order;
            $save->save();

        } else {
            return response()->json(['invalid_file_format'], 422);
        }

        return response()->json(['file_uploaded'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    public function reorder(Request $request)
    {
        $orders = $request->orders;
        $ids = array_column($orders, 'id');
        $imageProduct_ids = ProductImage::whereIn('id', $ids)->get()->pluck('product_id')->toArray();

        if (count(array_unique($imageProduct_ids)) === 1) { #All values in the $imageProductIds are the same
            $ords = array_column($orders, 'order');
            $diffOrder = count(array_unique($ords)) === count($ords); #Order of images are different
            $maxOrd = count($imageProduct_ids) >= max($ords); #Order's number must not exceed the number of images
            if ($diffOrder && $maxOrd) {
                $sellerProducts = User::findOrFail(auth()->id())->products;
                $sellerProductNames = $sellerProducts->pluck('name')->toArray();
                $productOfImage = ProductImage::findOrfail($ids[0])->product->name;
                if (in_array($productOfImage, $sellerProductNames)) {
                    foreach ($orders as $item) {
                        ProductImage::findOrFail($item['id'])->update([
                            'order' => $item['order']
                        ]);
                    }
                    return response()->json([
                        'status' => trans('shop.success'),
                        'message' => 'The order of images has changed successfully'
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => trans('shop.notFound'),
                        'message' => 'These are not your images, and you can\'t reorder them'
                    ], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json([
                    'status' => trans('shop.notFound'),
                    'message' => 'Order of images must be different and must not exceed the number of images'
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
