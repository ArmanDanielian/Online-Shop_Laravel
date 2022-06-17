<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRateRequest;
use App\Models\Order;
use App\Models\Rate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class RateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $rates = Rate::query()
            ->rateFilter($user);

        return response()->json([
            'status' => trans('shop.success'),
            'data' => $rates->get()
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRateRequest $request, Order $order): JsonResponse
    {
        $products = json_decode($order->products);
        try {
            if (!$order->is_rated) {
                DB::beginTransaction();
                foreach ($products as $index => $product) {
                    $rates = $request->validated()['rates'];
                    $comments = $request->validated()['comments'];
                    $data[$index] = Rate::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'rate' => $rates[$index],
                        'comment' => $comments[$index]
                    ]);
                }
                $order->update([
                    'is_rated' => 1
                ]);
                DB::commit();
                return response()->json([
                    'status' => trans('shop.success'),
                    'data' => $data
                ], Response::HTTP_OK);
            }
            return response()->json([
                'status' => trans('shop.notFound'),
                'message' => 'Already rated'
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'status' => trans('shop.notFound'),
                'message' => 'Something wrong'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order, Rate $rate)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): JsonResponse
    {
        $rate = Rate::findOrFail($id);
        if ($rate->user_id === auth()->id()) {
            $rate->update([
                'rate' => $request->rate
            ]);
            return response()->json([
                'status' => trans('shop.success'),
                'message' => 'Rate updated successfully'
            ], Response::HTTP_OK);
        } else return response()->json([
            'status' => trans('shop.notFound'),
            'message' => "It’s not yours and you can’t change rate"
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rate $rate): JsonResponse
    {
        #rate-ը կարա destroy անի մենակ admin-ը
        $rate->delete();
        return response()->json([
            'status' => trans('shop.success'),
            'message' => 'Rate deleted successfully'
        ], Response::HTTP_OK);
    }

    public function report(Request $request, Rate $rate)
    {
        $user = auth()->user();
        if ($user->isSeller()) {
            $shop = $rate->product->shop;
            if ($shop->isSellerShop()) {
                $rate->update([
                    'report_status' => 1,
                    'report_comment' => $request['report_comment']
                ]);
            } else {
                return response()->json([
                    'status' => trans('shop.notFound'),
                    'message' => 'You don\'t have rate'
                ], Response::HTTP_NOT_FOUND);
            }
        } elseif ($rate->report_status === 1) {  # admin-նա մտել
            $rate->update([
                'report_status' => 2,
                'report_comment' => $request['report_comment']
            ]);
        }
        return response()->json([
            'status' => trans('shop.success'),
            'data' => $rate
        ], Response::HTTP_OK);
    }
}
