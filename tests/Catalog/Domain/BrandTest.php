<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain;

use App\Catalog\Domain\Entity\Brand;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class BrandTest extends TestCase
{
    public function testImageIsStoredAndCanBeRemovedThroughBrandUpdate(): void
    {
        $now = new \DateTime('2026-01-01 00:00:00');
        $brand = Brand::create(
            'Acme',
            new Slug('acme'),
            null,
            null,
            null,
            null,
            0,
            true,
            $now,
            'uploads/brands/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png',
        );

        self::assertSame('uploads/brands/aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.png', $brand->getImage());

        $brand->update(
            'Acme',
            new Slug('acme'),
            null,
            null,
            null,
            null,
            0,
            true,
            new \DateTime('2026-01-02 00:00:00'),
            null,
        );

        self::assertNull($brand->getImage());
    }
}
