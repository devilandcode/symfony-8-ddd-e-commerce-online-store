<?php

declare(strict_types=1);

namespace App\Catalog\Application\Characteristic\AdminUseCases\ListChars;

use App\Catalog\Application\Characteristic\AdminUseCases\Output\CharacteristicViewDTO;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;

final readonly class ListCharsHandler
{
    public function __construct(private CharacteristicRepositoryInterface $characteristicRepository)
    {
    }

    /** @return list<CharacteristicViewDTO> */
    public function __invoke(): array
    {
        return array_map(
            CharacteristicViewDTO::fromCharacteristic(...),
            $this->characteristicRepository->findAll(),
        );
    }
}
