<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases;

use App\Catalog\Application\Category\AdminUseCases\Input\CategoryCharacteristicDataDTO;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;

final readonly class SyncCategoryCharacteristics
{
    public function __construct(private CharacteristicRepositoryInterface $characteristicRepository)
    {
    }

    /**
     * @param list<CategoryCharacteristicDataDTO> $assignments
     */
    public function __invoke(Category $category, array $assignments): void
    {
        $selectedIds = [];
        foreach ($assignments as $assignment) {
            if (isset($selectedIds[$assignment->characteristicId])) {
                throw new \InvalidArgumentException('A characteristic can only be assigned to a category once.');
            }

            $selectedIds[$assignment->characteristicId] = true;
        }

        $resolvedCharacteristics = [];
        foreach ($assignments as $assignment) {
            $resolvedCharacteristics[$assignment->characteristicId] =
                $this->characteristicRepository->findById($assignment->characteristicId)
                ?? throw new \InvalidArgumentException(sprintf(
                    'Characteristic with ID %d was not found.',
                    $assignment->characteristicId,
                ));
        }

        foreach ($category->getCharacteristicAssignments() as $existingAssignment) {
            $existingId = $existingAssignment->getCharacteristic()->getId();
            if (null !== $existingId && !isset($selectedIds[$existingId])) {
                $category->removeCharacteristic($existingAssignment->getCharacteristic());
            }
        }

        foreach ($assignments as $assignment) {
            $category->assignCharacteristic(
                $resolvedCharacteristics[$assignment->characteristicId],
                $assignment->isActive,
                $assignment->inFilter,
                new \DateTime(),
            );
        }
    }
}
