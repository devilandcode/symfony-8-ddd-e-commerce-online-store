<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Input;

final readonly class PageNumber
{
    public function __construct(public int $value = 1)
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('Page number must be positive.');
        }
    }
}
