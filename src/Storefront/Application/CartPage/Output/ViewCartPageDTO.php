<?php

declare(strict_types=1);

namespace App\Storefront\Application\CartPage\Output;

use App\Cart\Application\FrontUseCases\Output\CartDTO;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;

final readonly class ViewCartPageDTO
{
    public function __construct(
        public ViewPageDTO $page,
        public CartDTO $cart,
    ) {
    }
}
