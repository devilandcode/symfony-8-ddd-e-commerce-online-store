<?php

declare(strict_types=1);

namespace App\Storefront\Application\HomePage\Output;

use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;

final readonly class ViewHomePageDTO
{
    public function __construct(
        public ViewPageDTO $page,
        public ViewHomePageCatalogDTO $catalog,
    ) {
    }
}
