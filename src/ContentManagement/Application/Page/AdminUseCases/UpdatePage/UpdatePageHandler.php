<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\AdminUseCases\UpdatePage;

use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageNotFoundException;
use App\ContentManagement\Application\Page\AdminUseCases\Exception\PageSlugAlreadyInUseException;
use App\ContentManagement\Application\Page\AdminUseCases\Input\PageDataDTO;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;
use App\Shared\Application\FlusherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final readonly class UpdatePageHandler
{
    public function __construct(
        private PageRepositoryInterface $pageRepository,
        private FlusherInterface $flusher,
    ) {
    }

    public function __invoke(int $id, PageDataDTO $data): void
    {
        $page = $this->pageRepository->findById($id) ?? throw PageNotFoundException::withId($id);
        $existingPage = $this->pageRepository->findBySlug($data->slug);

        if (null !== $existingPage && $existingPage->getId() !== $page->getId()) {
            throw PageSlugAlreadyInUseException::withSlug($data->slug->toString());
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
            throw PageSlugAlreadyInUseException::withSlug($data->slug->toString(), $exception);
        }
    }
}
