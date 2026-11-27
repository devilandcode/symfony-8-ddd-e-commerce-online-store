<?php

declare(strict_types=1);

namespace App\Storefront\Application\CartPage\Handler;

use App\Cart\Application\FrontUseCases\GetCart\GetCartHandler;
use App\ContentManagement\Application\Page\FrontUseCases\FindBySlug\FindPageBySlugHandler;
use App\Shared\Domain\ValueObject\Slug;
use App\Storefront\Application\CartPage\Output\ViewCartPageDTO;

final readonly class GetCartPageHandler
{
    public function __construct(
        private FindPageBySlugHandler $findPageBySlug,
        private GetCartHandler $getCart,
    ) {
    }

    public function __invoke(): ViewCartPageDTO
    {
        return new ViewCartPageDTO(
            page: ($this->findPageBySlug)(new Slug('cart')),
            cart: ($this->getCart)(),
        );
    }
}
