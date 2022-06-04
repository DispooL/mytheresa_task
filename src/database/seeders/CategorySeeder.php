<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            'boots',
            'sneakers',
            'sandals',
        ];

        foreach ($categories as $category) {
            $newCategory = new Category();
            $newCategory->name = $category;

            $newCategory->save();
        }
    }
}
