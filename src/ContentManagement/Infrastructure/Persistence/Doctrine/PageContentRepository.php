<?php

declare(strict_types=1);

namespace App\ContentManagement\Infrastructure\Persistence\Doctrine;

use App\ContentManagement\Domain\Entity\PageContent;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageContentRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PageContentRepository implements PageContentRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findByPageId(int $pageId): ?PageContent
    {
        $page = $this->entityManager->getReference(Page::class, $pageId);
        $pageContent = $this->entityManager->getRepository(PageContent::class)->findOneBy(['page' => $page]);

        if (null !== $pageContent && !$pageContent instanceof PageContent) {
            throw new \LogicException('The page content repository returned an unexpected entity.');
        }

        return $pageContent;
    }

    public function save(PageContent $pageContent): void
    {
        $this->entityManager->persist($pageContent);
    }
}
