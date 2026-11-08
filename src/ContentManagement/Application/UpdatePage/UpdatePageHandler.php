<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\UpdatePage;

use App\ContentManagement\Application\Exception\PageNotFound;
use App\ContentManagement\Application\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Application\PageData;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class UpdatePageHandler
{
    public function __construct(private PageRepositoryInterface $pages)
    {
    }

    public function __invoke(int $id, PageData $data): void
    {
        $page = $this->pages->findById($id) ?? throw PageNotFound::withId($id);
        $existingPage = $this->pages->findBySlug($data->slug);

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

        $this->pages->save($page);
    }
}
