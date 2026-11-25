<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Output;

final readonly class CatalogCharacteristicFilterView
{
    /**
     * @param list<string> $values
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?string $measure,
        public array $values,
    ) {
    }
}
