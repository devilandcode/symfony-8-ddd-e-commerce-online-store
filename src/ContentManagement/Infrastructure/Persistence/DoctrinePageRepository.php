<?php

declare(strict_types=1);

namespace App\ContentManagement\Infrastructure\Persistence;

use App\ContentManagement\Application\Page\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Domain\ValueObject\Slug;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrinePageRepository implements PageRepositoryInterface
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
        $page = $this->entityManager->getRepository(Page::class)->findOneBy(['slugValue' => $slug->toString()]);

        if (null !== $page && !$page instanceof Page) {
            throw new \LogicException('The page repository returned an unexpected entity.');
        }

        return $page;
    }

    public function save(Page $page): void
    {
        $this->entityManager->persist($page);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            $slug = $page->getSlug();
            if (null === $slug) {
                throw $exception;
            }

            throw PageSlugAlreadyInUse::withSlug($slug->toString(), $exception);
        }
    }

    public function delete(Page $page): void
    {
        $this->entityManager->remove($page);
        $this->entityManager->flush();
    }
}
