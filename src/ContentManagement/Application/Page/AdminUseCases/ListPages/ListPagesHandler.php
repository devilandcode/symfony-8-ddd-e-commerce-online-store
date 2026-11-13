<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\AdminUseCases\ListPages;

use App\ContentManagement\Application\Page\AdminUseCases\Output\PageViewDTO;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class ListPagesHandler
{
    public function __construct(private PageRepositoryInterface $pageRepository)
    {
    }

    /**
     * @return list<PageViewDTO>
     */
    public function __invoke(): array
    {
        return array_map(PageViewDTO::fromPage(...), $this->pageRepository->findAll());
    }
}
