<?php

declare(strict_types=1);

namespace App\Catalog\Application\Category\AdminUseCases\UpdateCategory;

use App\Catalog\Application\Category\AdminUseCases\Exception\CategorySlugAlreadyInUseException;
use App\Catalog\Application\Category\AdminUseCases\Input\CategoryDataDTO;
use App\Catalog\Domain\Entity\Category;
use App\Catalog\Domain\Repository\CategoryRepositoryInterface;
use App\Shared\Application\FlusherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class UpdateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id, CategoryDataDTO $data): void
    {
        $category = $this->categoryRepository->findById($id);

        if (null === $category) {
            throw new \InvalidArgumentException(sprintf('Category with ID %d not found.', $id));
        }

        $parent = $this->parentFromId($data->parentId);

        $existing = $this->categoryRepository->findBySlug($data->slug);
        if (null !== $existing && $existing->getId() !== $category->getId()) {
            throw CategorySlugAlreadyInUseException::withSlug($data->slug->toString());
        }

        $category->update(
            $data->title,
            $data->slug,
            $data->h1,
            $parent,
            $data->level,
            $data->desc,
            $data->content,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
            $data->isActive,
            new \DateTime(),
        );

        $this->categoryRepository->save($category);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw CategorySlugAlreadyInUseException::withSlug($data->slug->toString(), $exception);
        }
    }

    private function parentFromId(?int $parentId): ?Category
    {
        if (null === $parentId) {
            return null;
        }

        return $this->categoryRepository->findById($parentId)
            ?? throw new \InvalidArgumentException(sprintf('Parent category with ID %d was not found.', $parentId));
    }
}
