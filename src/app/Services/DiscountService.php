<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class DiscountService
{
    /**
     * Adds discount to products
     * This method can be used for different scenarios
     * It can add a discount either for a single product or for many products at the same time
     *
     * But make sure that the same discount doesn't exist yet
     *
     * @param Collection<Product> $products
     * @param int                 $percentage
     *
     * @return void
     */
    public function addProductsDiscount(Collection $products, int $percentage): void
    {
        $discounts = [];

        foreach ($products as $product) {
            $discounts[] = [
                'product_id' => $product->id,
                'percentage' => $percentage
            ];
        }

        // In order to make efficient queries split array by chunks and insert by chunks
        $discounts = array_chunk($discounts, 500);

        foreach ($discounts as $discount) {
            // Insert all new discounts by 500 rows, it's significantly boosts our performance
            Discount::query()->insert($discount);
        }
    }
}
