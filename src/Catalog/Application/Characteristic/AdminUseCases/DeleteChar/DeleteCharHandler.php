<?php

declare(strict_types=1);

namespace App\Catalog\Application\Characteristic\AdminUseCases\DeleteChar;

use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharacteristicNotFoundException;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;
use App\Shared\Application\FlusherInterface;

final readonly class DeleteCharHandler
{
    public function __construct(
        private CharacteristicRepositoryInterface $characteristicRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id): void
    {
        $characteristic = $this->characteristicRepository->findById($id)
            ?? throw CharacteristicNotFoundException::withId($id);

        $this->characteristicRepository->delete($characteristic);
        $this->flusher->commit();
    }
}
