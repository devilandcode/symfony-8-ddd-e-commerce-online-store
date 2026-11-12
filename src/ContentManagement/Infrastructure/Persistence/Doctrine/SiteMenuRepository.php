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
        return $this->entityManager->createQueryBuilder()
            ->select('siteMenu', 'parent')
            ->from(SiteMenu::class, 'siteMenu')
            ->leftJoin('siteMenu.parent', 'parent')
            ->orderBy('siteMenu.pos', \SortDirection::Ascending)
            ->addOrderBy('siteMenu.id', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function findActiveRoots(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('siteMenu', 'children')
            ->distinct()
            ->from(SiteMenu::class, 'siteMenu')
            ->leftJoin('siteMenu.children', 'children', 'WITH', 'children.is_active = true')
            ->where('siteMenu.parent IS NULL')
            ->andWhere('siteMenu.is_active = true')
            ->orderBy('siteMenu.pos', \SortDirection::Ascending)
            ->addOrderBy('siteMenu.id', \SortDirection::Ascending)
            ->getQuery()
            ->getResult();
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
        $siteMenu = $this->entityManager->getRepository(SiteMenu::class)->findOneBy(['slug.value' => $slug->toString()]);

        if (null !== $siteMenu && !$siteMenu instanceof SiteMenu) {
            throw new \LogicException('The site menu repository returned an unexpected entity.');
        }

        return $siteMenu;
    }

    public function save(SiteMenu $siteMenu): void
    {
        $this->entityManager->persist($siteMenu);
    }

    public function delete(SiteMenu $siteMenu): void
    {
        $this->entityManager->remove($siteMenu);
    }
}
