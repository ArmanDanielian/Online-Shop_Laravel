<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function carts()
    {
        return $this->hasMany(CartItem::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function isShopProduct($shop): bool
    {
        return $this->shop_id === $shop->id;
    }

    public function scopeSearch($query, $text)
    {
        $query
            ->select('shops.name as shop_name', 'products.*')
            ->leftjoin('shops', 'shops.id', '=', 'products.shop_id')
            ->where('shops.name', 'like', "%$text%")
            ->orWhere('products.name', 'like', "%$text%")
            ->orWhere('products.description', 'like', "%$text%");
    }
}
