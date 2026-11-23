<?php

declare(strict_types=1);

namespace App\Catalog\Application\HomePage;

final readonly class HomePageProductView
{
    public function __construct(
        public string $title,
        public string $brandTitle,
        public ?string $image,
        public string $price,
        public ?string $oldPrice,
        public bool $isNew,
        public bool $isHit,
    ) {
    }
}
