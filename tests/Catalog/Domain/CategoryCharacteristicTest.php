<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain;

use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Entity\Characteristic;
use App\Catalog\Application\Category\AdminUseCases\Output\CategoryViewDTO;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class CategoryCharacteristicTest extends TestCase
{
    public function testCategoryCanAssignUpdateAndRemoveCharacteristic(): void
    {
        $now = new \DateTime();
        $category = Category::create('Cameras', new Slug('cameras'), null, null, 0, null, null, null, null, 0, true, $now);
        $characteristic = Characteristic::create('Resolution', ' MP ', 0, true, $now);
        (new \ReflectionProperty(Characteristic::class, 'id'))->setValue($characteristic, 7);

        $category->assignCharacteristic($characteristic, true, true, $now);
        self::assertCount(1, $category->getCharacteristicAssignments());
        self::assertTrue($category->getCharacteristicAssignments()[0]->isActive());
        self::assertTrue($category->getCharacteristicAssignments()[0]->isInFilter());

        (new \ReflectionProperty(Category::class, 'id'))->setValue($category, 3);
        $view = CategoryViewDTO::fromCategory($category);
        self::assertSame(7, $view->characteristicAssignments[0]->characteristicId);
        self::assertTrue($view->characteristicAssignments[0]->isActive);
        self::assertTrue($view->characteristicAssignments[0]->inFilter);

        $category->assignCharacteristic($characteristic, false, false, new \DateTime());
        self::assertCount(1, $category->getCharacteristicAssignments());
        self::assertFalse($category->getCharacteristicAssignments()[0]->isActive());
        self::assertFalse($category->getCharacteristicAssignments()[0]->isInFilter());

        $category->removeCharacteristic($characteristic);
        self::assertSame([], $category->getCharacteristicAssignments());
        self::assertSame('MP', $characteristic->getMeasure());
    }

    public function testCharacteristicRequiresNonEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Characteristic::create('  ', null, 0, true, new \DateTime());
    }

    public function testCharacteristicNormalizesMeasureAndBuildsDisplayTitle(): void
    {
        $now = new \DateTime();
        $withMeasure = Characteristic::create('Resolution', '  MP  ', 0, true, $now);
        $withoutMeasure = Characteristic::create('Color', '   ', 1, true, $now);

        self::assertSame('MP', $withMeasure->getMeasure());
        self::assertSame('Resolution (MP)', $withMeasure->getTitleWithMeasure());
        self::assertNull($withoutMeasure->getMeasure());
        self::assertSame('Color', $withoutMeasure->getTitleWithMeasure());
    }
}
