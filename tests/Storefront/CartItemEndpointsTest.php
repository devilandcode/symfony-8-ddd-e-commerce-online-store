<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Cart\Application\FrontUseCases\Output\CartProductDTO;
use App\Cart\Application\FrontUseCases\Output\PurchasableProductDTO;
use App\Cart\Application\Interface\CartProductProviderInterface;
use App\Cart\Application\Interface\PurchasableProductProviderInterface;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Application\FlusherInterface;
use App\Tests\Cart\Double\CountingFlusher;
use App\Tests\Cart\Double\FakeCartProductProvider;
use App\Tests\Cart\Double\FakeProductProvider;
use App\Tests\Cart\Double\InMemoryCartRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

final class CartItemEndpointsTest extends WebTestCase
{
    private const string CSRF_TOKEN = 'test-csrf-token';

    private KernelBrowser $client;
    private InMemoryCartRepository $carts;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $container = static::getContainer();

        $this->carts = new InMemoryCartRepository();
        $container->set(CartRepositoryInterface::class, $this->carts);
        $container->set(FlusherInterface::class, new CountingFlusher());
        $container->set(PurchasableProductProviderInterface::class, new FakeProductProvider([
            1 => new PurchasableProductDTO(1, true, 150),
            2 => new PurchasableProductDTO(2, true, 3),
            3 => new PurchasableProductDTO(3, false, 3),
        ]));
        $container->set(CartProductProviderInterface::class, new FakeCartProductProvider([
            1 => new CartProductDTO(1, 'Perfume', 'Burberry', null, 1000, 2000, 'BYN', 150),
            2 => new CartProductDTO(2, 'Cream', 'Dior', null, 550, null, 'BYN', 3),
        ]));

        $session = $container->get('session.factory')->createSession();
        $session->set('_csrf/app', self::CSRF_TOKEN);
        $session->save();
        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        $this->send('POST', '/cart/add', ['productId' => 1, 'quantity' => 2]);
        $this->send('POST', '/cart/add', ['productId' => 2, 'quantity' => 1]);
    }

    public function testUpdateReturnsLineTotalsAndCartCount(): void
    {
        $response = $this->send('PUT', '/cart/update', ['productId' => 1, 'quantity' => 3]);

        self::assertResponseStatusCodeSame(200);
        self::assertTrue($response['success']);
        self::assertSame(4, $response['cartCount']);
        self::assertFalse($response['isEmpty']);
        self::assertSame([
            'productId' => 1,
            'quantity' => 3,
            'availableUnits' => 150,
            'canIncrease' => true,
            'total' => '30.00 BYN',
            'oldTotal' => '60.00 BYN',
        ], $response['item']);
        self::assertSame(
            ['currencyCode' => 'BYN', 'products' => '65.50 BYN', 'discount' => '30.00 BYN', 'total' => '35.50 BYN'],
            $response['totals'][0],
        );
    }

    public function testQuantityAboveThePreviousFixedCapIsAllowedWhenStockPermits(): void
    {
        $response = $this->send('PUT', '/cart/update', ['productId' => 1, 'quantity' => 150]);

        self::assertResponseStatusCodeSame(200);
        self::assertSame(150, $response['item']['quantity']);
        self::assertFalse($response['item']['canIncrease']);
    }

    public function testQuantityAboveStockIsUnprocessable(): void
    {
        $this->send('PUT', '/cart/update', ['productId' => 2, 'quantity' => 4]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testZeroOrNegativeQuantityIsRejectedByRequestValidation(): void
    {
        $this->send('PUT', '/cart/update', ['productId' => 1, 'quantity' => 0]);
        self::assertResponseStatusCodeSame(422);

        $this->send('PUT', '/cart/update', ['productId' => 1, 'quantity' => -1]);
        self::assertResponseStatusCodeSame(422);
    }

    public function testUpdateOfAProductNotInTheCartReturnsNotFound(): void
    {
        $response = $this->send('PUT', '/cart/update', ['productId' => 3, 'quantity' => 1]);

        self::assertResponseStatusCodeSame(404);
        self::assertFalse($response['success']);
    }

    public function testRemoveReturnsTheRemainingCart(): void
    {
        $response = $this->send('DELETE', '/cart/remove', ['productId' => 1]);

        self::assertResponseStatusCodeSame(200);
        self::assertSame(1, $response['cartCount']);
        self::assertSame(1, $response['removedProductId']);
        self::assertFalse($response['isEmpty']);
        self::assertSame('5.50 BYN', $response['totals'][0]['total']);
    }

    public function testRemovingTheLastProductReportsAnEmptyCart(): void
    {
        $this->send('DELETE', '/cart/remove', ['productId' => 1]);
        $response = $this->send('DELETE', '/cart/remove', ['productId' => 2]);

        self::assertSame(0, $response['cartCount']);
        self::assertTrue($response['isEmpty']);
        self::assertSame([], $response['totals']);
    }

    public function testRemoveOfAProductNotInTheCartReturnsNotFound(): void
    {
        $this->send('DELETE', '/cart/remove', ['productId' => 3]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testBothEndpointsRejectAMissingCsrfToken(): void
    {
        $this->send('PUT', '/cart/update', ['productId' => 1, 'quantity' => 3], csrfToken: '');
        self::assertResponseStatusCodeSame(403);

        $this->send('DELETE', '/cart/remove', ['productId' => 1], csrfToken: '');
        self::assertResponseStatusCodeSame(403);

        self::assertSame(2, $this->carts->all()[0]->getItems()[0]->getQuantity()->getValue());
        self::assertCount(2, $this->carts->all()[0]->getItems());
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function send(string $method, string $url, array $payload, string $csrfToken = self::CSRF_TOKEN): array
    {
        $this->client->request($method, $url, server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $csrfToken,
        ], content: json_encode($payload, JSON_THROW_ON_ERROR));

        return json_decode((string) $this->client->getResponse()->getContent(), true) ?? [];
    }
}
