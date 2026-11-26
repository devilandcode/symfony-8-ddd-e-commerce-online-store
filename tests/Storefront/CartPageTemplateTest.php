<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Cart\Application\FrontUseCases\Output\CartDTO;
use App\Cart\Application\FrontUseCases\Output\CartItemDTO;
use App\Cart\Application\FrontUseCases\Output\CartTotalDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

final class CartPageTemplateTest extends KernelTestCase
{
    public function testEmptyCartRendersTheEmptyState(): void
    {
        $html = $this->renderCart(CartDTO::empty());

        self::assertStringContainsString('w-cart-empty', $html);
        self::assertStringNotContainsString('w-cart-list-item', $html);
        self::assertStringNotContainsString('Сумма заказа', $html);
    }

    public function testCartLinesAndTotalsAreRenderedFromTheCart(): void
    {
        $html = $this->renderCart(new CartDTO(
            [
                new CartItemDTO(10, 'Weekend', 'Burberry', null, 2, 10000, 20000, 'BYN'),
                new CartItemDTO(11, 'Cream', 'Dior', null, 1, 550, null, 'BYN'),
            ],
            [new CartTotalDTO('BYN', 40550, 20000, 20550)],
        ));

        self::assertSame(2, substr_count($html, 'class="w-cart-list-item"'));
        self::assertStringContainsString('Weekend', $html);
        self::assertStringContainsString('Burberry', $html);
        self::assertStringContainsString('value="2"', $html);
        self::assertStringContainsString('200.00 BYN', $html);
        self::assertStringContainsString('400.00 BYN', $html);
        self::assertStringContainsString('405.50 BYN', $html);
        self::assertStringContainsString('205.50 BYN', $html);
        self::assertStringNotContainsString('w-cart-empty', $html);
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
                ->renderBlock('content', ['cart' => $cart]);
        } finally {
            $requestStack->pop();
        }
    }
}
