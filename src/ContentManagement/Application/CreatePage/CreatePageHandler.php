<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\CreatePage;

use App\ContentManagement\Application\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Application\PageData;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class CreatePageHandler
{
    public function __construct(private PageRepositoryInterface $pages)
    {
    }

    public function __invoke(PageData $data): void
    {
        $slug = $data->slug;
        if (null !== $this->pages->findBySlug($slug)) {
            throw PageSlugAlreadyInUse::withSlug($slug->toString());
        }

        $page = Page::create(
            $data->title,
            $slug,
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
