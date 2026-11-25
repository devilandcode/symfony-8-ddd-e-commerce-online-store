<?php

declare(strict_types=1);

namespace App\Storefront\Application\CatalogPage\Handler;

use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\Shared\Domain\ValueObject\Slug;
use App\Storefront\Application\CatalogPage\CatalogPageComposer;
use App\Storefront\Application\CatalogPage\Input\CatalogPageFilters;
use App\Storefront\Application\CatalogPage\Input\PageNumber;
use App\Storefront\Application\CatalogPage\Output\ViewCatalogPageDTO;

final readonly class GetRootCatalogPageHandler
{
    public function __construct(
        private FindPageBySlugHandler $findPageBySlug,
        private CatalogPageComposer $composer,
    ) {
    }

    public function __invoke(CatalogPageFilters $filters, PageNumber $pageNumber): ViewCatalogPageDTO
    {
        return $this->composer->compose(
            ($this->findPageBySlug)(new Slug('catalog')),
            null,
            $filters,
            $pageNumber,
        );
    }
}
