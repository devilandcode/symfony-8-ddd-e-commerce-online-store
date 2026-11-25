<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Storefront\Infrastructure\Http\Request\CatalogPageQueryParameters;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class CatalogPageQueryParametersTest extends TestCase
{
    public function testItMapsValidatedParametersToApplicationFilters(): void
    {
        $query = new CatalogPageQueryParameters();
        $query->page = 2;
        $query->categories = ['2', '2', '4'];
        $query->brands = ['7'];
        $query->characteristics = [11 => [' Eau de parfum ', '']];

        self::assertCount(0, self::validator()->validate($query));
        self::assertSame(2, $query->toPageNumber()->value);
        self::assertSame([2, 4], $query->toFilters()->categoryIds);
        self::assertSame([7], $query->toFilters()->brandIds);
        self::assertSame([11 => ['Eau de parfum']], $query->toFilters()->characteristicValues);
    }

    public function testItRejectsInvalidPageAndFilterValues(): void
    {
        $query = new CatalogPageQueryParameters();
        $query->page = 0;
        $query->brands = ['invalid', '9999999999999999999999999'];
        $query->characteristics = ['invalid-id' => ['value']];

        $violations = self::validator()->validate($query);

        self::assertGreaterThanOrEqual(3, $violations->count());
    }

    private static function validator(): \Symfony\Component\Validator\Validator\ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }
}
