<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Cart\Application\FrontUseCases\Output\CartDTO;
use App\Cart\Application\FrontUseCases\Output\CartItemDTO;
use App\Cart\Application\FrontUseCases\Output\CartTotalDTO;
use App\ContentManagement\Application\Page\FrontUseCases\Output\PageContentView;
use App\ContentManagement\Application\Page\FrontUseCases\Output\ViewPageDTO;
use App\Storefront\Application\CartPage\Output\ViewCartPageDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class CartPageTemplateTest extends KernelTestCase
{
    public function testEmptyCartRendersTheEmptyState(): void
    {
        $html = $this->renderCart(CartDTO::empty());

        self::assertStringContainsString('w-cart-empty xl-pt-40 pt-20 _js-cart-empty"', $html);
        self::assertStringNotContainsString('w-cart-list-item', $html);
        self::assertStringNotContainsString('Сумма заказа', $html);
    }

    public function testCartLinesAndTotalsAreRenderedFromTheCart(): void
    {
        $html = $this->renderCart(new CartDTO(
            [
                new CartItemDTO(10, 'Weekend', 'Burberry', null, 2, 10000, 20000, 'BYN', 5, '100 мл', '1W - Fair Warm'),
                new CartItemDTO(11, 'Cream', 'Dior', null, 1, 550, null, 'BYN', 1),
            ],
            [new CartTotalDTO('BYN', 40550, 20000, 20550)],
        ));

        self::assertSame(2, substr_count($html, 'class="w-cart-list-item _js-cart-item"'));
        self::assertStringContainsString('<h1 class="pagetitle _h2 upper fontCor semibold color-dblue">Корзина</h1>', $html);
        self::assertStringContainsString('Weekend', $html);
        self::assertStringContainsString('Burberry', $html);
        self::assertStringContainsString('value="2"', $html);
        self::assertStringContainsString('200.00 BYN', $html);
        self::assertStringContainsString('400.00 BYN', $html);
        self::assertStringContainsString('405.50 BYN', $html);
        self::assertStringContainsString('205.50 BYN', $html);
        self::assertStringContainsString('w-cart-empty xl-pt-40 pt-20 hidden _js-cart-empty', $html);
    }

    public function testQuantityControlsDeleteLinkAndOptionalRowsAreRendered(): void
    {
        $html = $this->renderCart(new CartDTO(
            [
                new CartItemDTO(10, 'Weekend', 'Burberry', null, 2, 10000, 20000, 'BYN', 5, '100 мл', '1W - Fair Warm'),
                new CartItemDTO(11, 'Cream', 'Dior', null, 1, 550, null, 'BYN', 1),
            ],
            [new CartTotalDTO('BYN', 40550, 20000, 20550)],
        ));

        self::assertSame(2, substr_count($html, '_js-b-minus'));
        self::assertSame(2, substr_count($html, '_js-b-plus'));
        self::assertSame(2, substr_count($html, '_js-b-remove'));
        self::assertSame(2, substr_count($html, '_js-pcscontrolls'));

        // Only the product with more than one unit in stock may be increased, only the one with quantity 2 decreased.
        self::assertSame(1, substr_count($html, 'btn fcm left minus _js-b-minus"'));
        self::assertSame(1, substr_count($html, 'btn fcm right plus _js-b-plus"'));

        self::assertStringContainsString('50%', $html);
        self::assertStringContainsString('100 мл', $html);
        self::assertStringContainsString('1W - Fair Warm', $html);
        self::assertSame(1, substr_count($html, 'round-sticker-default'));
        self::assertSame(1, substr_count($html, 'Цвет:'));
    }

    public function testQuantityInputExposesProductIdCountAndMaxForCartJs(): void
    {
        $html = $this->renderCart(new CartDTO(
            [
                new CartItemDTO(10, 'Weekend', 'Burberry', null, 2, 10000, 20000, 'BYN', 5),
                new CartItemDTO(11, 'Cream', 'Dior', null, 1, 550, null, 'BYN', 1),
            ],
            [new CartTotalDTO('BYN', 40550, 20000, 20550)],
        ));

        self::assertStringContainsString('data-product-id="10" data-count="2" data-max="5"', $html);
        self::assertStringContainsString('data-product-id="11" data-count="1" data-max="1"', $html);
        self::assertSame(2, substr_count($html, 'data-max='));
    }

    private function renderCart(CartDTO $cart): string
    {
        self::bootKernel();
        $request = Request::create('/cart');
        $request->attributes->set('_route', 'cart.index');
        $request->attributes->set('_route_params', []);
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);

        try {
            return static::getContainer()->get('twig')
                ->load('cart.html.twig')
                ->renderBlock('content', ['cartPage' => new ViewCartPageDTO(
                    new ViewPageDTO('Cart', 'Корзина', null, null, null, null, new PageContentView([])),
                    $cart,
                )]);
        } finally {
            $requestStack->pop();
        }
    }
}
