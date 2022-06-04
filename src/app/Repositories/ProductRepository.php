<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProductDTO;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Product Repository
 *
 * Has methods to work with product
 */
class ProductRepository
{
    /**
     * Finds products by given category and filters by price
     *
     * @param ProductDTO $productDTO
     * @param int        $paginateItems
     *
     * @return LengthAwarePaginator
     */
    public function products(ProductDTO $productDTO, int $paginateItems = 5): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'discounts'])
            ->when($productDTO->category_id, static function ($query) use ($productDTO) {
                return $query->where('category_id', $productDTO->category_id);
            })
            ->when($productDTO->price && $productDTO->operator, function ($query) use ($productDTO) {
                return $query->where('original_price', $productDTO->operator, $productDTO->price);
            })
            ->paginate($paginateItems);
    }
}
