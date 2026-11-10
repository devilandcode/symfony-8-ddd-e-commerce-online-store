<?php

namespace App\ContentManagement\Infrastructure\Persistence\Doctrine;

use App\ContentManagement\Domain\Entity\SiteMenu;
use App\ContentManagement\Domain\Repository\SiteMenuRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use Doctrine\ORM\EntityManagerInterface;

class SiteMenuRepository implements SiteMenuRepositoryInterface 
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public function findAll(): array
    {
        return $this->entityManager->getRepository(SiteMenu::class)->findBy([], ['pos' => 'ASC', 'id' => 'ASC']);
    }

    public function findById(int $id): ?SiteMenu
    {
        $siteMenu = $this->entityManager->getRepository(SiteMenu::class)->find($id);

        if (null !== $siteMenu && !$siteMenu instanceof SiteMenu) {
            throw new \LogicException('The site menu repository returned an unexpected entity.');
        }

        return $siteMenu;
    }

    public function findBySlug(Slug $slug): ?SiteMenu
    {
        $siteMenu = $this->entityManager->getRepository(SiteMenu::class)->findOneBy(['slugValue' => $slug->toString()]);

        if (null !== $siteMenu && !$siteMenu instanceof SiteMenu) {
            throw new \LogicException('The site menu repository returned an unexpected entity.');
        }

        return $siteMenu;
    }

    public function save(SiteMenu $siteMenu): void
    {
        $this->entityManager->persist($siteMenu);
        $this->entityManager->flush();
    }

    public function delete(SiteMenu $siteMenu): void
    {
        $this->entityManager->remove($siteMenu);
        $this->entityManager->flush();
    }
}
