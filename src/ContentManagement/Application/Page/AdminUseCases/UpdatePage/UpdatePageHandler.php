<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\AdminUseCases\UpdatePage;

use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageNotFound;
use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Application\Page\AdminUseCases\Input\PageDataDTO;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Application\Flusher;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class UpdatePageHandler
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(int $id, PageDataDTO $data): void
    {
        $page = $this->pageRepository->findById($id) ?? throw PageNotFound::withId($id);
        $existingPage = $this->pageRepository->findBySlug($data->slug);

        if (null !== $existingPage && $existingPage->getId() !== $page->getId()) {
            throw PageSlugAlreadyInUse::withSlug($data->slug->toString());
        }

        $page->update(
            $data->title,
            $data->slug,
            $data->h1,
            $data->description,
            $data->content,
            $data->seoTitle,
            $data->seoDescription,
            $data->position,
            $data->isActive,
            new \DateTime(),
        );

        $this->pageRepository->save($page);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw PageSlugAlreadyInUse::withSlug($data->slug->toString(), $exception);
        }
    }
}
