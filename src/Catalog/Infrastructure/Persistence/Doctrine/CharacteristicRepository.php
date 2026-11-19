<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine;

use App\Catalog\Domain\Entity\Characteristic;
use App\Catalog\Domain\Repository\CharacteristicRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final readonly class CharacteristicRepository implements CharacteristicRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public function findBy(string $title, ?string $measure): ?Characteristic
    {
        $characteristic = $this->entityManager->getRepository(Characteristic::class)
            ->findOneBy(['title' => trim($title), 'measure' => null === $measure || '' === trim($measure) ? null : trim($measure)]);

        if (null !== $characteristic && !$characteristic instanceof Characteristic) {
            throw new LogicException('The characteristic repository returned an unexpected entity.');
        }

        return $characteristic;
    }

    public function findById(int $id): ?Characteristic
    {
        $characteristic = $this->entityManager->getRepository(Characteristic::class)->find($id);

        if (null !== $characteristic && !$characteristic instanceof Characteristic) {
            throw new LogicException('The characteristic repository returned an unexpected entity.');
        }

        return $characteristic;
    }

    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('characteristic')
            ->from(Characteristic::class, 'characteristic')
            ->orderBy('characteristic.pos', \SortDirection::Ascending)
            ->addOrderBy('characteristic.title', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
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
