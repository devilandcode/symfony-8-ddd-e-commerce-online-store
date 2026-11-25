<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Request;

use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback(callback: 'validateQueryParameters')]
final class CatalogPageQueryParameters
{
    #[Assert\Positive]
    public int $page = 1;

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Regex('/\A[1-9][0-9]*\z/'),
    ])]
    public array $categories = [];

    /** @var list<string> */
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Regex('/\A[1-9][0-9]*\z/'),
    ])]
    public array $brands = [];

    /**
     * @var array<int, list<string>>
     */
    #[Assert\Type('array')]
    public array $characteristics = [];

    public function toFilters(): CatalogPageFilters
    {
        $characteristicValues = [];
        foreach ($this->characteristics as $id => $values) {
            $values = array_values(array_unique(array_filter(
                array_map(trim(...), $values),
                static fn (string $value): bool => '' !== $value,
            )));

            if ([] !== $values) {
                $characteristicValues[(int) $id] = $values;
            }
        }

        return new CatalogPageFilters(
            categoryIds: array_values(array_unique(array_map(intval(...), $this->categories))),
            brandIds: array_values(array_unique(array_map(intval(...), $this->brands))),
            characteristicValues: $characteristicValues,
        );
    }

    public function validateQueryParameters(ExecutionContextInterface $context): void
    {
        foreach (['categories' => $this->categories, 'brands' => $this->brands] as $filterName => $ids) {
            foreach ($ids as $index => $id) {
                if (false === filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                    $context->buildViolation('Filter identifiers must be positive integers within the supported range.')
                        ->atPath(sprintf('%s[%d]', $filterName, $index))
                        ->addViolation();
                }
            }
        }

        foreach ($this->characteristics as $id => $values) {
            if (!is_int($id) || $id < 1 || !is_array($values) || !array_is_list($values)) {
                $context->buildViolation('Each characteristic filter must use a positive ID and a list of values.')
                    ->atPath('characteristics')
                    ->addViolation();

                continue;
            }

            foreach ($values as $value) {
                if (!is_string($value)) {
                    $context->buildViolation('Characteristic filter values must be strings.')
                        ->atPath(sprintf('characteristics[%d]', $id))
                        ->addViolation();
                }
            }
        }
    }
}
