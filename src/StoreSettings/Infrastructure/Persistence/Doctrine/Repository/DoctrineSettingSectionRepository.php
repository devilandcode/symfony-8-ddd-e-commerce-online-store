<?php

declare(strict_types=1);

namespace App\StoreSettings\Infrastructure\Persistence\Doctrine\Repository;

use App\StoreSettings\Domain\Entity\SettingSection;
use App\StoreSettings\Domain\Enum\SettingSectionKey;
use App\StoreSettings\Domain\Repository\SettingSectionRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSettingSectionRepository implements SettingSectionRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findByKey(SettingSectionKey $key): ?SettingSection
    {
        $section = $this->entityManager->getRepository(SettingSection::class)->findOneBy(['key' => $key]);

        if (null !== $section && !$section instanceof SettingSection) {
            throw new \LogicException('The settings repository returned an unexpected entity.');
        }

        return $section;
    }

    public function save(SettingSection $section): void
    {
        $this->entityManager->persist($section);
        $this->entityManager->flush();
    }
}
