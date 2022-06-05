<?php

namespace App\Models;

use Carbon\Carbon;
use GeneaLabs\LaravelModelCaching\CachedModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Category
 *
 * Stores categories
 * The model is cached in Redis by default
 *
 * @property int                      $id
 *
 * @property string                   $name
 *
 * @property Carbon                   $created_at
 * @property Carbon                   $updated_at
 *
 * @property-read Collection<Product> $products
 */
class Category extends CachedModel
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
