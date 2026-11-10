<?php

declare(strict_types=1);

namespace App\ContentManagement\Infrastructure\Persistence\Doctrine;

use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PageRepository implements PageRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findAll(): array
    {
        /** @var list<Page> $pages */
        $pages = $this->entityManager->getRepository(Page::class)->findBy([], ['pos' => 'ASC', 'id' => 'ASC']);

        return $pages;
    }

    public function findById(int $id): ?Page
    {
        $page = $this->entityManager->getRepository(Page::class)->find($id);

        if (null !== $page && !$page instanceof Page) {
            throw new \LogicException('The page repository returned an unexpected entity.');
        }

        return $page;
    }

    public function findBySlug(Slug $slug): ?Page
    {
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(['slug.value' => $slug->toString()]);

        if (null !== $page && !$page instanceof Page) {
            throw new \LogicException('The page repository returned an unexpected entity.');
        }

        return $page;
    }

    public function save(Page $page): void
    {
        $this->entityManager->persist($page);
    }

    public function delete(Page $page): void
    {
        $this->entityManager->remove($page);
    }
}
