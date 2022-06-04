<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * ProductDTO stores Product properties but since we only have one endpoint
 * It only has two properties
 */
class ProductDTO
{
    /**
     * @var int|null
     */
    public ?int $category_id;
    /**
     * @var int|null
     */
    public ?int $price;
    /**
     * @var string|null
     */
    public ?string $operator;

    /**
     * @param array $data
     *
     * @return ProductDTO
     */
    public static function createFromArrray(array $data): ProductDTO
    {
        $dto = new self();

        $dto->category_id = isset($data['category_id']) ? (int)$data['category_id'] : null;
        $dto->price = isset($data['price']) ? (int)$data['price'] : null;
        $dto->operator = $data['operator'] ?? null;

        return $dto;
    }
}
