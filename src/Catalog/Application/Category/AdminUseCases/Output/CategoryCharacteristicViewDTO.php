<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\Output;

use App\Catalog\Domain\Entity\CategoryCharacteristic;

final readonly class CategoryCharacteristicViewDTO
{
    public function __construct(
        public int $characteristicId,
        public string $title,
        public ?string $measure,
        public bool $isActive,
        public bool $inFilter,
    ) {
    }

    public static function fromAssignment(CategoryCharacteristic $assignment): self
    {
        $characteristic = $assignment->getCharacteristic();
        $id = $characteristic->getId();
        if (null === $id) {
            throw new \LogicException('Persisted characteristic is missing an id.');
        }

        return new self(
            $id,
            $characteristic->getTitle(),
            $characteristic->getMeasure(),
            $assignment->isActive(),
            $assignment->isInFilter(),
        );
    }
}
