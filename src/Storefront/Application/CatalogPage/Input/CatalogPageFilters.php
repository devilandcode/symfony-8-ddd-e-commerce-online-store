<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Input;

final readonly class CatalogPageFilters
{
    /**
     * @param list<int> $categoryIds
     * @param list<int> $brandIds
     * @param array<int, list<string>> $characteristicValues
     */
    public function __construct(
        public array $categoryIds = [],
        public array $brandIds = [],
        public array $characteristicValues = [],
    ) {
    }
}
