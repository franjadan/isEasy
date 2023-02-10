<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProduct;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::factory()->count(20)->create();

        foreach($products as $product){
            $storeProduct = new StoreProduct;
            $storeProduct->create([
                'store_id' => Store::all()->random()->id,
                'product_id' => $product->id,
                'quantity' => random_int(0,10),
            ]);
            
        }

    }
}
