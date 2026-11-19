<?php

declare(strict_types=1);

namespace App\Catalog\Application\Product\AdminUseCases\Output;

use App\Catalog\Domain\Entity\ProductCharacteristic;

final readonly class ProductCharacteristicViewDTO
{
    public function __construct(
        public int $characteristicId,
        public string $title,
        public ?string $measure,
        public string $value,
    ) {
    }

    public static function fromAssignment(ProductCharacteristic $assignment): self
    {
        $characteristic = $assignment->getCharacteristic();
        $id = $characteristic->getId() ?? throw new \LogicException('Persisted characteristic is missing an id.');

        return new self($id, $characteristic->getTitle(), $characteristic->getMeasure(), $assignment->getValue());
    }
}
