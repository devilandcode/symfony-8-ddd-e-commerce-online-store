<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\UpdatePage;

use App\ContentManagement\Application\Page\Command\PageData;
use App\ContentManagement\Application\Page\Exception\PageNotFound;
use App\ContentManagement\Application\Page\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class UpdatePageHandler
{
    public function __construct(private PageRepositoryInterface $pageRepository)
    {
    }

    public function __invoke(int $id, PageData $data): void
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
    }
}
