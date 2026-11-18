<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine;

use App\Catalog\Domain\Entity\Characteristic;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final readonly class CharacteristicRepostory implements CharacteristicRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public function findBy(string $title, ?string $measure): ?Characteristic
    {
       $char = $this->entityManager->getRepository(Characteristic::class)
            ->findOneBy(['title' => $title, 'measure' => $measure]);

        if (null !== $char && !$char instanceof Characteristic) {
            throw new LogicException('The characteristic repository returned an unexpected entity.');            
        }

        return $char;
    }
    
    public function save(Characteristic $characteristic): void
    {
        $this->entityManager->persist($characteristic);
    }

    public function delete(Characteristic $characteristic): void
    {
        $this->entityManager->remove($characteristic);
    }
}
