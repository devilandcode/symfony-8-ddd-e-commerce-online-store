<?php

declare(strict_types=1);

namespace App\Storefront\Application\HomePage\Output;

final readonly class HomePageBrandView
{
    public function __construct(
        public string $title,
        public ?string $image = null,
    ) {
    }
}
