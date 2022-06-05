<?php

namespace Tests\Feature;

use App\DTO\ProductDTO;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * /products endpoint test
     */
    public function test_products_shown_correctly()
    {
        // Find products and pass it to ProductResource
        $productDTO = ProductDTO::createFromArrray(data: []);
        /* @var Collection<Product> $products */
        $products = (new ProductRepository())->products(productDTO: $productDTO);
        $resource = ProductResource::collection($products);

        // Assert that response has the same data and structure
        $this->json('get', '/api/products')
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonFragment(
                [
                    'data' => json_decode($resource->toJson(), true, 512, JSON_THROW_ON_ERROR),
                ]
            );
    }

    /**
     * /products endpoint with filters test
     */
    public function test_products_with_filters_shown_correctly()
    {
        // Find products by filters and pass it to ProductResource
        $filters = [
            'category'    => 'boots',
            'category_id' => 1, // boots
            'price'       => 80000,
            'operator'    => '<=',
        ];
        $productDTO = ProductDTO::createFromArrray(data: $filters);
        /* @var Collection<Product> $products */
        $products = (new ProductRepository())->products(productDTO: $productDTO);
        $resource = ProductResource::collection($products);

        // Assert that response has the same data and structure
        $this->json('get', '/api/products', $filters)
            ->assertStatus(ResponseAlias::HTTP_OK)
            ->assertJsonFragment(
                [
                    'data' => json_decode($resource->toJson(), true, 512, JSON_THROW_ON_ERROR),
                ]
            );
    }

    /**
     * Test validation for /products endpoint
     */
    public function test_products_validation()
    {
        $this->assertExactValidationRules(
            [
                'category' => 'string',
                'price'    => 'required_with:operator|integer',
                'operator' => 'required_with:price|string|in:<,<=,>,>=,=',
            ],
            (new ProductRequest())->rules()
        );
    }
}
