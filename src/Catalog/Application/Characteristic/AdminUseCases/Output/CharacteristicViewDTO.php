<?php

declare(strict_types=1);

namespace App\Catalog\Application\Characteristic\AdminUseCases\Output;

use App\Catalog\Domain\Entity\Characteristic;

final readonly class CharacteristicViewDTO
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $measure,
        public int $position,
        public bool $isActive,
    ) {
    }

    public static function fromCharacteristic(Characteristic $characteristic): self
    {
        $id = $characteristic->getId();
        if (null === $id) {
            throw new \LogicException('Persisted characteristic is missing an id.');
        }

        return new self(
            $id,
            $characteristic->getTitle(),
            $characteristic->getMeasure(),
            $characteristic->getPos(),
            $characteristic->getIsActive(),
        );
    }
}
