<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * SkuCast custom cast for Product model to add leading zeros to sku field
 */
class SkuCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        // Find how many zeros is in $value and then add the other ones
        $zeros_to_add = 5 - (int) preg_replace('/[^0]+/', '', (string) $value);

        return str_pad((string) $value, $zeros_to_add, '0', STR_PAD_LEFT);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return ltrim((string) $value, "0");
    }
}
