<?php

namespace Tests\Feature;

use App\DTO\ProductDTO;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_discount_to_product()
    {
        // Find random product
        $products = Product::query()->inRandomOrder()->take(1)->get();
        /* @var Product $product */
        $product = $products->first();
        // Add 50 percent discount on it
        (new DiscountService())->addProductsDiscount(products: $products, percentage: 50);
        // Assert that the discount has been created
        $this->assertDatabaseHas('discounts', [
            'product_id' => $product->id,
            'percentage' => 50,
        ]);
    }
}
