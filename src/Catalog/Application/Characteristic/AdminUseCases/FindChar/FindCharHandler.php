<?php

declare(strict_types=1);

namespace App\Catalog\Application\Characteristic\AdminUseCases\FindChar;

use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharacteristicNotFoundException;
use App\Catalog\Application\Characteristic\AdminUseCases\Output\CharacteristicViewDTO;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;

final readonly class FindCharHandler
{
    public function __construct(private CharacteristicRepositoryInterface $characteristicRepository)
    {
    }

    public function __invoke(int $id): CharacteristicViewDTO
    {
        $characteristic = $this->characteristicRepository->findById($id)
            ?? throw CharacteristicNotFoundException::withId($id);

        return CharacteristicViewDTO::fromCharacteristic($characteristic);
    }
}
