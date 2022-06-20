<?php

namespace App\Http\Requests;

use App\Models\ProductImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderProductImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'orders' => ['array'],
            'orders.*.id' => ['required', 'integer', 'min:0', 'distinct', 'exists:product_images'],
            'orders.*.order' => ['required', 'integer', 'min:0', 'max:' . $this->maxOrdQuantity(), 'distinct']
        ];
    }

    private function maxOrdQuantity()
    {
        $orders = request()->input('orders');
        $ids = array_column($orders, 'id');
        $imagesCount  = ProductImage::whereIn('id', $ids)->count();
        return $imagesCount;
    }
}
