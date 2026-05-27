<?php

namespace App\DTO;

readonly class ProductCreateData
{
    public function __construct(
        public string $name,
        public ?string $description,
        public int $categoryId,
        public int $conditionId,
    ) {
    }
}
