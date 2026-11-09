<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\ListPages;

use App\ContentManagement\Application\Page\Query\PageView;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class ListPagesHandler
{
    public function __construct(private PageRepositoryInterface $pageRepository)
    {
    }

    /**
     * @return list<PageView>
     */
    public function __invoke(): array
    {
        return array_map(PageView::fromPage(...), $this->pageRepository->findAll());
    }
}
