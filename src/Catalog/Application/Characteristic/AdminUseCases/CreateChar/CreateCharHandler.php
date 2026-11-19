<?php

declare(strict_types=1);

namespace App\Catalog\Application\Characteristic\AdminUseCases\CreateChar;

use App\Catalog\Application\Characteristic\AdminUseCases\Exception\CharAlreadyInUseException;
use App\Catalog\Application\Characteristic\AdminUseCases\Input\CharDataDTO;
use App\Catalog\Domain\Entity\Characteristic;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;
use App\Shared\Application\FlusherInterface;
final readonly class CreateCharHandler
{
    public function __construct(
        private CharacteristicRepositoryInterface $charRepository,
        private FlusherInterface $flusher,
    ){}

    public function __invoke(CharDataDTO $data): void
    {
        $char = $this->charRepository->findBy($data->title, $data->measure);

        if (null !== $char) {
            throw CharAlreadyInUseException::withTitle($data->title);
        }

        $char = Characteristic::create(
            title: $data->title,
            measure: $data->measure,
            position: $data->position,
            isActive: $data->isActive,
            updatedAt: new \DateTime(),
        );

        $this->charRepository->save($char);

        $this->flusher->commit();
    }
}
