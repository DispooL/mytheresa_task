<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Services\DiscountService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $discountService = new DiscountService();

        // Find boots products

        /* @var Collection<Product> $boots */
        $boots = Category::query()->where('name', 'boots')->first()->products;
        $product = Product::query()->where('sku', 3)->get();

        // Add 30% discount on boots
        $discountService->addProductsDiscount(products: $boots, percentage: 30);
        // Add 15% discount on product with sku 000003
        $discountService->addProductsDiscount(products: $product, percentage: 15);
    }
}
