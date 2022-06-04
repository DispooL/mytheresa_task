<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;

/**
 * Category Repository
 *
 * Has methods to work with category
 */
class CategoryRepository
{
    /**
     * Finds category by name
     *
     * @param string $categoryName
     *
     * @return Category|null
     */
    public function getByName(string $categoryName): ?Category
    {
        // I use noinspection because otherwise PHPStorm would say that I use wrong type here when I use the right one
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Category::query()
            ->where('name', $categoryName)
            ->first();
    }
}
