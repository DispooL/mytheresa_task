<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Discount
 *
 * Stores data about discounts that related to products
 *
 * @property int          $id
 * @property int          $product_id
 * @property int          $percentage
 *
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 *
 * @property-read Product $product
 */
class Discount extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'product_id',
        'percentage',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
