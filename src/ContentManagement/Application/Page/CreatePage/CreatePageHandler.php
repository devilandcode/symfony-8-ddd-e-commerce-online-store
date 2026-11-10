<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\CreatePage;

use App\ContentManagement\Application\Page\Exception\PageSlugAlreadyInUse;
use App\ContentManagement\Application\Page\Input\PageDataDTO;
use App\ContentManagement\Domain\Entity\Page;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Application\Flusher;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class CreatePageHandler
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private Flusher $flusher,
    ) {
    }

    public function __invoke(PageDataDTO $data): void
    {
        $slug = $data->slug;
        if (null !== $this->pageRepository->findBySlug($slug)) {
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

        $this->pageRepository->save($page);

        try {
            $this->flusher->commit();
        } catch (UniqueConstraintViolationException $exception) {
            throw PageSlugAlreadyInUse::withSlug($slug->toString(), $exception);
        }
    }
}
