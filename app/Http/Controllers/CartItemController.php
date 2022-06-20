<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $data = CartItem::where('user_id', auth()->id())->with('product:id,name,description')->get();
        if ($data->isNotEmpty()) {
            return response()->json([
                'status' => trans('shop.success'),
                'data' => $data
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => trans('shop.notFound'),
                'message' => 'Your cart is empty'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $item = CartItem::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'count' => $request->count
        ]);

        return response()->json([
            'status' => trans('shop.success'),
            'message' => 'Cart created successfully',
            'item' => $item
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id === auth()->id()) {
            $cartItem->update([
                'count' => $request->count
            ]);
            return response()->json([
                'status' => trans('shop.success'),
                'message' => 'Cart updated successfully'
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => trans('shop.notFound'),
                'message' => 'This isn\'t your item'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id === auth()->id()) {
            $cartItem->delete();
            return response()->json([
                'status' => trans('shop.success'),
                'message' => 'Cart deleted successfully',
                'item' => $cartItem
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => trans('shop.notFound'),
                'message' => 'This isn\'t your item'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    public function checkout()
    {
        $cartItems = CartItem::where('user_id', auth()->id())->with('product')->get();
        $subtotal = $cartItems->map(function ($item) {
            return $item->product->price * $item->count;
        })->sum();
        $products = $cartItems->map(function ($item) {
            return [
                'id' => $item->product->id,
                'name' => $item->product->name,
                'count' => $item->count,
                'price' => $item->product->price
            ];
        });
        Order::create([
            'user_id' => auth()->id(),
            'products' => json_encode($products),
            'subtotal' => $subtotal
        ]);
        foreach ($cartItems as $item) {
            $item->delete();
        };
        return response()->json([
            'status' => trans('shop.success'),
            'message' => 'Thank you for shopping'
        ], Response::HTTP_OK);
    }
}
