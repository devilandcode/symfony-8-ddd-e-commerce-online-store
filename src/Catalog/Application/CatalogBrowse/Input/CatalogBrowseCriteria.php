<?php

declare(strict_types=1);

namespace App\Catalog\Application\CatalogBrowse\Input;

final readonly class CatalogBrowseCriteria
{
    /**
     * @param list<int> $categoryIds
     * @param list<int> $brandIds
     * @param array<int, list<string>> $characteristicValues
     */
    public function __construct(
        public ?string $categorySlug = null,
        public array $categoryIds = [],
        public array $brandIds = [],
        public array $characteristicValues = [],
    ) {
        if (null !== $categorySlug && '' === trim($categorySlug)) {
            throw new \InvalidArgumentException('Category slug cannot be empty.');
        }

        self::assertPositiveIds($categoryIds, 'Category ids');
        self::assertPositiveIds($brandIds, 'Brand ids');

        foreach ($characteristicValues as $id => $values) {
            if (!is_int($id) || $id < 1 || !is_array($values) || !array_is_list($values) || [] === $values) {
                throw new \InvalidArgumentException('Each characteristic filter must use a positive id and a list of values.');
            }

            foreach ($values as $value) {
                if (!is_string($value) || '' === trim($value)) {
                    throw new \InvalidArgumentException('Characteristic filter values cannot be empty.');
                }
            }
        }
    }

    /** @param list<int> $ids */
    private static function assertPositiveIds(array $ids, string $label): void
    {
        foreach ($ids as $id) {
            if (!is_int($id) || $id < 1) {
                throw new \InvalidArgumentException($label.' must be positive integers.');
            }
        }
    }
}
