<?php

namespace App\Http\Controllers;

use App\DTO\ProductDTO;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Index method, returns products by given filters
     *
     * @param ProductRequest     $request
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository  $productRepository
     *
     * @return AnonymousResourceCollection
     */
    public function index(ProductRequest $request, CategoryRepository $categoryRepository, ProductRepository $productRepository): AnonymousResourceCollection
    {
        // Validate data
        $validated = $request->validated();
        $validated['category_id'] = isset($validated['category']) ? $categoryRepository->getByName(categoryName: $validated['category'])->id : null;

        // Create ProductDTO for querying products
        $productDTO = ProductDTO::createFromArrray(data: $validated);
        $products = $productRepository->products(productDTO: $productDTO);

        return ProductResource::collection($products);
    }
}
