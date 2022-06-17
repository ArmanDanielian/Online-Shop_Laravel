<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Rate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $productId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $product = Product::find($this->productId);
        $avgRate = Rate::query()
            ->where('product_id', '=', $this->productId)
            ->avg('rate');
        $product->update([
            'rating' => $avgRate
        ]);
    }
}
