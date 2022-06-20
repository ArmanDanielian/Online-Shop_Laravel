<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $orders  = Order::query()
            ->when($user->isBuyer(), function () use ($user) {
                return $user->orders();
            });
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $orders->get()
        ], JsonResponse::HTTP_OK);
    }
}
