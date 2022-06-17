<?php

namespace App\Http\Controllers;


use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $shops = Shop::query()
            ->forSeller($user);
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $shops->get()
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreShopRequest $request): JsonResponse
    {
        $shop = Shop::create($request->validated());
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $shop
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        $user = auth()->user();
        $shop = Shop::query()
            ->forSeller($user)
            ->findOrFail($id);
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $shop
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateShopRequest $request, Shop $shop): JsonResponse
    {
        $shop->update($request->validated());
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $shop
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop): JsonResponse
    {
        $user = auth()->user();
        if ($user->isSeller()) {
            if ($shop->isSellerShop()) {
                $shop->delete();
            } else {
                return response()->json([
                    'status' => trans('shop.notFound'),
                    'message' => 'You don\'t have shop'
                ], Response::HTTP_NOT_FOUND);
            }
        } else {
            $shop->delete();
        }
        return response()->json([
            'status' => trans('shop.success'),
            'message' => 'Shop successfully deleted'
        ], Response::HTTP_OK);
    }
}
