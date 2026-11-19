<?php

declare(strict_types=1);

namespace App\Catalog\Application\Characteristic\AdminUseCases\UpdateChar;

use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharAlreadyInUseException;
use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharacteristicNotFoundException;
use App\Catalog\Application\Characteristic\AdminUseCases\Input\CharDataDTO;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;
use App\Shared\Application\FlusherInterface;

final readonly class UpdateCharHandler
{
    public function __construct(
        private CharacteristicRepositoryInterface $characteristicRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id, CharDataDTO $data): void
    {
        $characteristic = $this->characteristicRepository->findById($id)
            ?? throw CharacteristicNotFoundException::withId($id);
        $existing = $this->characteristicRepository->findBy($data->title, $data->measure);
        if (null !== $existing && $existing->getId() !== $id) {
            throw CharAlreadyInUseException::withTitle($data->title);
        }

        $characteristic->update(
            $data->title,
            $data->measure,
            $data->position,
            $data->isActive,
            new \DateTime(),
        );

        $this->characteristicRepository->save($characteristic);
        $this->flusher->commit();
    }
}
