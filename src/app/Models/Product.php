<?php

namespace App\Models;

use App\Casts\PriceCast;
use App\Casts\SkuCast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Product
 *
 * Stores data about product and also contains computed fields
 *
 * @property int                  $id
 * @property int                  $category_id
 * @property int                  $discount_percentage
 *
 * @property string               $sku
 * @property string               $name
 *
 * @property float                $original_price
 * @property float                $final_price
 *
 * @property-read Collection<Discount> $discounts
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'original_price',
    ];

    protected $casts = [
        'sku'            => SkuCast::class,
    ];

    protected $appends = [
        'final_price',
        'discount_percentage',
    ];

    /**
     * Computes final_price
     *
     * @return float
     */
    public function getFinalPriceAttribute(): int
    {
        $discountPercentage = $this->calculateDiscountPercentage();

        // If the product doesn't have any discounts return the original price
        if ($discountPercentage === null) {
            return $this->original_price;
        }

        return $this->original_price * (100 - $discountPercentage) / 100;
    }

    /**
     * Computes discount_percentage
     *
     * @return string|null
     */
    public function getDiscountPercentageAttribute(): string|null
    {
        $discountPercentage = $this->calculateDiscountPercentage();

        return $discountPercentage ? $discountPercentage .'%' : null;
    }

    /**
     * @return HasMany
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculates discount percentage for current model
     *
     * @return int|null
     */
    private function calculateDiscountPercentage(): int|null
    {
        $discountPercentage = null;
        foreach ($this->discounts as $discount) {
            // If current $discount is more than previous one, assign $appliedDiscount to it
            $discountPercentage = ((int)$discountPercentage < $discount->percentage) ? $discount->percentage : $discountPercentage;
        }

        return $discountPercentage;
    }
}
