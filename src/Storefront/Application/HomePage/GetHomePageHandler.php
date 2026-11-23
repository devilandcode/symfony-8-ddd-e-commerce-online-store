<?php

declare(strict_types=1);

namespace App\Storefront\Application\HomePage;

use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\Shared\Domain\ValueObject\Slug;

final readonly class GetHomePageHandler
{
    public function __construct(
        private FindPageBySlugHandler $findPageBySlug,
        private GetHomePageCatalogHandler $getHomePageCatalog,
    ) {
    }

    public function __invoke(): ViewHomePageDTO
    {
        return new ViewHomePageDTO(
            page: ($this->findPageBySlug)(new Slug('home')),
            catalog: ($this->getHomePageCatalog)(),
        );
    }
}
