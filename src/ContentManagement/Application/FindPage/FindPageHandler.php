<?php

declare(strict_types=1);

namespace App\ContentManagement\Application\FindPage;

use App\ContentManagement\Application\Exception\PageNotFound;
use App\ContentManagement\Application\PageView;
use App\ContentManagement\Domain\Repository\PageRepositoryInterface;

final readonly class FindPageHandler
{
    public function __construct(private PageRepositoryInterface $pages)
    {
    }

    public function __invoke(int $id): PageView
    {
        $page = $this->pages->findById($id) ?? throw PageNotFound::withId($id);

        return PageView::fromPage($page);
    }
}
