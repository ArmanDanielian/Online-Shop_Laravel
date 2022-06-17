<?php

namespace App\Console\Commands;

use App\Jobs\UpdateRates;
use App\Models\Product;
use App\Models\Rate;
use Illuminate\Console\Command;

class ProductRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rate:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Product::query()
            ->select('id')
            ->chunk(10, function ($products) {
                foreach ($products as $product) {
                    dispatch(new UpdateRates($product->id));
                }
            });
        return 0;
    }
}
