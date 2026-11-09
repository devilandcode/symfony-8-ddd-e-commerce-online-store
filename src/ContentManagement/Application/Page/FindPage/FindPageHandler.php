<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\Page\FindPage;

use App\ContentManagement\Application\Page\Exception\PageNotFound;
use App\ContentManagement\Application\Page\Query\PageView;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class FindPageHandler
{
    public function __construct(private PageRepositoryInterface $pageRepository)
    {
    }

    public function __invoke(int $id): PageView
    {
        $page = $this->pageRepository->findById($id) ?? throw PageNotFound::withId($id);

        return PageView::fromPage($page);
    }
}
