<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First get the categories

        /* @var Collection<Category> $categories */
        $categories = Category::query()->get();

        // Second create products related to the categories

        Product::query()->insert(
            [
                [
                    'sku'            => 1,
                    'name'           => 'BV Lean leather ankle boots',
                    'category_id'    => $categories->where('name', 'boots')->first()->id,
                    'original_price' => 89000,
                    'currency'       => 'EUR',
                ],
                [
                    'sku'            => 2,
                    'name'           => 'BV Lean leather ankle boots',
                    'category_id'    => $categories->where('name', 'boots')->first()->id,
                    'original_price' => 99000,
                    'currency'       => 'EUR',
                ],
                [
                    'sku'            => 3,
                    'name'           => 'Ashlington leather ankle boots',
                    'category_id'    => $categories->where('name', 'boots')->first()->id,
                    'original_price' => 71000,
                    'currency'       => 'EUR',
                ],
                [
                    'sku'            => 4,
                    'name'           => 'Naima embellished suede sandals',
                    'category_id'    => $categories->where('name', 'sandals')->first()->id,
                    'original_price' => 79500,
                    'currency'       => 'EUR',
                ],
                [
                    'sku'            => 5,
                    'name'           => 'Nathane leather sneakers',
                    'category_id'    => $categories->where('name', 'sneakers')->first()->id,
                    'original_price' => 59000,
                    'currency'       => 'EUR',
                ],
            ]
        );
    }
}
