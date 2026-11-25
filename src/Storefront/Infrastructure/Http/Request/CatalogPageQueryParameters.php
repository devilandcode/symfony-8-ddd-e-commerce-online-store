<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Request;

use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Input\PageNumber;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/** HTTP shape of the catalog query string. Only shape, type and range checks live here (-> 400). */
final class CatalogPageQueryParameters
{
    private const POSITIVE_INT = '/\A[1-9][0-9]{0,17}\z/';

    #[Assert\Positive]
    public int $page = 1;

    /** @var list<string> */
    #[Assert\All([new Assert\Type('string'), new Assert\Regex(self::POSITIVE_INT)])]
    public array $categories = [];

    /** @var list<string> */
    #[Assert\All([new Assert\Type('string'), new Assert\Regex(self::POSITIVE_INT)])]
    public array $brands = [];

    /**
     * Characteristic id => selected values.
     *
     * @var array<int, list<string>>
     */
    #[Assert\All([new Assert\Type('array'), new Assert\All([new Assert\Type('string')])])]
    #[Assert\Callback([self::class, 'validateCharacteristicIds'])]
    public array $characteristics = [];

    public function toPageNumber(): PageNumber
    {
        return new PageNumber($this->page);
    }

    public function toFilters(): CatalogPageFilters
    {
        $characteristicValues = [];
        foreach ($this->characteristics as $id => $values) {
            $values = array_values(array_unique(array_filter(array_map(trim(...), $values), static fn (string $v): bool => '' !== $v)));
            if ([] !== $values) {
                $characteristicValues[$id] = $values;
            }
        }

        return new CatalogPageFilters(
            categoryIds: array_values(array_unique(array_map(intval(...), $this->categories))),
            brandIds: array_values(array_unique(array_map(intval(...), $this->brands))),
            characteristicValues: $characteristicValues,
        );
    }

    /** @param array<array-key, mixed> $characteristics */
    public static function validateCharacteristicIds(array $characteristics, ExecutionContextInterface $context): void
    {
        foreach (array_keys($characteristics) as $id) {
            if (!is_int($id) || $id < 1) {
                $context->buildViolation('Characteristic ids must be positive integers.')->addViolation();

                return;
            }
        }
    }
}
