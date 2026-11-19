<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application;

use App\Catalog\Application\Category\AdminUseCases\Input\CategoryCharacteristicDataDTO;
use App\Catalog\Application\Category\AdminUseCases\SyncCategoryCharacteristics;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Entity\Characteristic;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use PHPUnit\Framework\TestCase;

final class SyncCategoryCharacteristicsTest extends TestCase
{
    public function testItReplacesAssignmentsAndKeepsBothFlagsFalseForSelectedRows(): void
    {
        $now = new \DateTime();
        $oldCharacteristic = Characteristic::create('Old', null, 0, true, $now);
        $newCharacteristic = Characteristic::create('New', null, 0, true, $now);
        (new \ReflectionProperty(Characteristic::class, 'id'))->setValue($oldCharacteristic, 1);
        (new \ReflectionProperty(Characteristic::class, 'id'))->setValue($newCharacteristic, 2);

        $category = Category::create('Category', new Slug('category'), null, null, 0, null, null, null, null, 0, true, $now);
        $category->assignCharacteristic($oldCharacteristic, true, true, $now);

        $repository = new class($newCharacteristic) implements CharacteristicRepositoryInterface {
            public function __construct(private Characteristic $characteristic)
            {
            }

            public function findBy(string $title, ?string $measure): ?Characteristic
            {
                return null;
            }

            public function findById(int $id): ?Characteristic
            {
                return 2 === $id ? $this->characteristic : null;
            }

            public function findAll(): array
            {
                return [$this->characteristic];
            }

            public function save(Characteristic $characteristic): void
            {
            }

            public function delete(Characteristic $characteristic): void
            {
            }
        };

        (new SyncCategoryCharacteristics($repository))(
            $category,
            [new CategoryCharacteristicDataDTO(2, false, false)],
        );

        $assignments = $category->getCharacteristicAssignments();
        self::assertCount(1, $assignments);
        self::assertSame($newCharacteristic, $assignments[0]->getCharacteristic());
        self::assertFalse($assignments[0]->isActive());
        self::assertFalse($assignments[0]->isInFilter());
    }

    public function testItRejectsDuplicateCharacteristicIds(): void
    {
        $now = new \DateTime();
        $category = Category::create('Category', new Slug('category'), null, null, 0, null, null, null, null, 0, true, $now);
        $repository = new class implements CharacteristicRepositoryInterface {
            public function findBy(string $title, ?string $measure): ?Characteristic
            {
                return null;
            }

            public function findById(int $id): ?Characteristic
            {
                return null;
            }

            public function findAll(): array
            {
                return [];
            }

            public function save(Characteristic $characteristic): void
            {
            }

            public function delete(Characteristic $characteristic): void
            {
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        (new SyncCategoryCharacteristics($repository))(
            $category,
            [
                new CategoryCharacteristicDataDTO(2, true, true),
                new CategoryCharacteristicDataDTO(2, false, false),
            ],
        );
    }

    public function testItRejectsAnUnknownCharacteristicBeforeChangingAssignments(): void
    {
        $now = new \DateTime();
        $category = Category::create('Category', new Slug('category'), null, null, 0, null, null, null, null, 0, true, $now);
        $existing = Characteristic::create('Existing', null, 0, true, $now);
        (new \ReflectionProperty(Characteristic::class, 'id'))->setValue($existing, 1);
        $category->assignCharacteristic($existing, true, true, $now);
        $repository = new class implements CharacteristicRepositoryInterface {
            public function findBy(string $title, ?string $measure): ?Characteristic
            {
                return null;
            }

            public function findById(int $id): ?Characteristic
            {
                return null;
            }

            public function findAll(): array
            {
                return [];
            }

            public function save(Characteristic $characteristic): void
            {
            }

            public function delete(Characteristic $characteristic): void
            {
            }
        };

        try {
            (new SyncCategoryCharacteristics($repository))(
                $category,
                [new CategoryCharacteristicDataDTO(404, true, true)],
            );
            self::fail('Sync should reject an unknown characteristic.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('404', $exception->getMessage());
            self::assertCount(1, $category->getCharacteristicAssignments());
        }
    }
}
