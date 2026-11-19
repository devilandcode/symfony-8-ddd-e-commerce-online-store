<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases;

use App\Catalog\Application\Product\AdminUseCases\Input\ProductCharacteristicDataDTO;
use App\Catalog\Domain\Entity\Product;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;

final readonly class SyncProductCharacteristics
{
    public function __construct(private CharacteristicRepositoryInterface $characteristicRepository)
    {
    }

    /** @param list<ProductCharacteristicDataDTO> $assignments */
    public function __invoke(Product $product, array $assignments): void
    {
        $byId = [];
        foreach ($assignments as $assignment) {
            if (isset($byId[$assignment->characteristicId])) {
                throw new \InvalidArgumentException('A characteristic can only be assigned to a product once.');
            }

            $byId[$assignment->characteristicId] = $assignment;
        }

        $resolved = [];
        foreach ($assignments as $assignment) {
            $resolved[$assignment->characteristicId] =
                $this->characteristicRepository->findById($assignment->characteristicId)
                ?? throw new \InvalidArgumentException(sprintf(
                    'Characteristic with ID %d was not found.',
                    $assignment->characteristicId,
                ));
        }

        foreach ($product->getCharacteristicAssignments() as $existingAssignment) {
            $id = $existingAssignment->getCharacteristic()->getId();
            if (null !== $id && (!isset($byId[$id]) || '' === trim($byId[$id]->value))) {
                $product->removeCharacteristic($existingAssignment->getCharacteristic());
            }
        }

        foreach ($assignments as $assignment) {
            if ('' !== trim($assignment->value)) {
                $product->assignCharacteristic($resolved[$assignment->characteristicId], $assignment->value);
            }
        }
    }
}
