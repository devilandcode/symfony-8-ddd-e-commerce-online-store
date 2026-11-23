<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain;

use App\Catalog\Domain\Entity\Brand;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Entity\Characteristic;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\ValueObject\Balance;
use App\Catalog\Domain\ValueObject\OldPrice;
use App\Catalog\Domain\ValueObject\Price;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testOldPriceMustExceedCurrentPrice(): void
    {
        $now = new \DateTime();
        $category = Category::create('Cameras', new Slug('cameras'), null, null, 0, null, null, null, null, 0, true, $now);
        $brand = Brand::create('Acme', new Slug('acme'), null, null, null, null, 0, true, $now);

        $this->expectException(\InvalidArgumentException::class);

        Product::create(
            'Camera',
            new Slug('camera'),
            null,
            null,
            new Price(1000, 'BYN'),
            new OldPrice(1000),
            new Balance(5),
            $category,
            $brand,
            null,
            null,
            null,
            null,
            0,
            false,
            false,
            true,
            $now,
        );
    }

    public function testProductCanHaveNoOldPrice(): void
    {
        $now = new \DateTime();
        $category = Category::create('Cameras', new Slug('cameras'), null, null, 0, null, null, null, null, 0, true, $now);
        $brand = Brand::create('Acme', new Slug('acme'), null, null, null, null, 0, true, $now);

        $product = Product::create(
            'Camera',
            new Slug('camera'),
            null,
            null,
            new Price(1000, 'BYN'),
            null,
            new Balance(5),
            $category,
            $brand,
            null,
            null,
            null,
            null,
            0,
            true,
            false,
            true,
            $now,
        );

        self::assertNull($product->getOldPrice());
        self::assertSame($category, $product->getCategory());
        self::assertSame($brand, $product->getBrand());
        self::assertTrue($product->isNew());
        self::assertFalse($product->isHit());

        $product->update(
            'Camera',
            new Slug('camera'),
            null,
            null,
            new Price(1000, 'BYN'),
            null,
            new Balance(5),
            $category,
            $brand,
            null,
            null,
            null,
            null,
            0,
            false,
            true,
            true,
            $now,
        );

        self::assertFalse($product->isNew());
        self::assertTrue($product->isHit());
    }

    public function testProductKeepsOldPriceAsAValueObjectWhenItExceedsCurrentPrice(): void
    {
        $now = new \DateTime();
        $category = Category::create('Cameras', new Slug('cameras'), null, null, 0, null, null, null, null, 0, true, $now);
        $brand = Brand::create('Acme', new Slug('acme'), null, null, null, null, 0, true, $now);
        $oldPrice = new OldPrice(1200);

        $product = Product::create(
            'Camera',
            new Slug('camera'),
            null,
            null,
            new Price(1000, 'BYN'),
            $oldPrice,
            new Balance(5),
            $category,
            $brand,
            null,
            null,
            null,
            null,
            0,
            false,
            false,
            true,
            $now,
        );

        self::assertSame($oldPrice, $product->getOldPrice());
    }

    public function testProductCanStoreValuesForCharacteristicsAssignedToItsCategory(): void
    {
        $now = new \DateTime();
        $category = Category::create('Cameras', new Slug('cameras'), null, null, 0, null, null, null, null, 0, true, $now);
        $brand = Brand::create('Acme', new Slug('acme'), null, null, null, null, 0, true, $now);
        $characteristic = Characteristic::create('Resolution', 'MP', 0, true, $now);
        $category->assignCharacteristic($characteristic, true, false, $now);
        $product = Product::create(
            'Camera',
            new Slug('camera'),
            null,
            null,
            new Price(1000, 'BYN'),
            null,
            new Balance(5),
            $category,
            $brand,
            null,
            null,
            null,
            null,
            0,
            false,
            false,
            true,
            $now,
        );

        $product->assignCharacteristic($characteristic, ' 24 MP ');

        self::assertCount(1, $product->getCharacteristicAssignments());
        self::assertSame('24 MP', $product->getCharacteristicAssignments()[0]->getValue());
        $product->assignCharacteristic($characteristic, '32 MP');
        self::assertCount(1, $product->getCharacteristicAssignments());
        self::assertSame('32 MP', $product->getCharacteristicAssignments()[0]->getValue());
        $product->removeCharacteristic($characteristic);
        self::assertSame([], $product->getCharacteristicAssignments());
    }

    public function testProductCannotStoreACharacteristicNotAssignedToItsCategory(): void
    {
        $now = new \DateTime();
        $category = Category::create('Cameras', new Slug('cameras'), null, null, 0, null, null, null, null, 0, true, $now);
        $brand = Brand::create('Acme', new Slug('acme'), null, null, null, null, 0, true, $now);
        $product = Product::create(
            'Camera',
            new Slug('camera'),
            null,
            null,
            new Price(1000, 'BYN'),
            null,
            new Balance(5),
            $category,
            $brand,
            null,
            null,
            null,
            null,
            0,
            false,
            false,
            true,
            $now,
        );

        $this->expectException(\InvalidArgumentException::class);
        $product->assignCharacteristic(Characteristic::create('Resolution', 'MP', 0, true, $now), '24 MP');
    }
}
