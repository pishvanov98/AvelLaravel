<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProductInitRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:product-init-redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mass_prod_id=[];
        $products_id=DB::connection('mysql2')->table('sd_product')->select('product_id')->where('quantity','>',0)->where('price','>',0)->latest('product_id')->get();
        $products_id->each(function ($item) use(&$mass_prod_id){
            $mass_prod_id[]=$item->product_id;
        });

        $products= app('Product')->ProductInit($mass_prod_id);

    }
}