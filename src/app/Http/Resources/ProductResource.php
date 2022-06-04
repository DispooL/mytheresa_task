<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ProductResource this is a representation of products json response structure
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'sku'      => $this->sku,
            'name'     => $this->name,
            'category' => $this->category->name,
            'price'    => [
                'original_price'      => $this->original_price,
                'final_price'         => $this->final_price,
                'discount_percentage' => $this->discount_percentage,
                'currency'            => $this->currency,
            ],
        ];
    }
}
