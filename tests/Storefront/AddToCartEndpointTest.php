<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use App\Cart\Application\FrontUseCases\Output\PurchasableProductDTO;
use App\Cart\Application\Interface\PurchasableProductProviderInterface;
use App\Cart\Domain\Repository\CartRepositoryInterface;
use App\Shared\Application\FlusherInterface;
use App\Tests\Cart\Double\CountingFlusher;
use App\Tests\Cart\Double\FakeProductProvider;
use App\Tests\Cart\Double\InMemoryCartRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

final class AddToCartEndpointTest extends WebTestCase
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
            1 => new PurchasableProductDTO(1, true, 5),
            2 => new PurchasableProductDTO(2, false, 5),
        ]));

        $session = $container->get('session.factory')->createSession();
        $session->set('_csrf/app', self::CSRF_TOKEN);
        $session->save();
        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }

    public function testAddsProductAndReturnsCartCount(): void
    {
        $response = $this->post(['productId' => 1, 'quantity' => 2]);

        self::assertResponseStatusCodeSame(200);
        self::assertSame(['success' => true, 'cartCount' => 2, 'cartUrl' => '/cart'], $response);
        self::assertCount(1, $this->carts->all());
    }

    public function testRepeatedAddKeepsASingleCartLine(): void
    {
        $this->post(['productId' => 1, 'quantity' => 1]);
        $response = $this->post(['productId' => 1, 'quantity' => 1]);

        self::assertSame(2, $response['cartCount'], 'The counter is the sum of quantities, not the number of lines.');
        self::assertCount(1, $this->carts->all()[0]->getItems());
        self::assertSame(2, $this->carts->all()[0]->getItems()[0]->getQuantity()->getValue());
    }

    public function testRejectsMissingCsrfToken(): void
    {
        $response = $this->post(['productId' => 1], csrfToken: '');

        self::assertResponseStatusCodeSame(403);
        self::assertFalse($response['success']);
        self::assertSame([], $this->carts->all());
    }

    public function testUnknownProductReturnsNotFound(): void
    {
        $this->post(['productId' => 404]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testInactiveProductReturnsConflict(): void
    {
        $this->post(['productId' => 2]);

        self::assertResponseStatusCodeSame(409);
    }

    public function testQuantityAboveStockReturnsUnprocessableEntity(): void
    {
        $this->post(['productId' => 1, 'quantity' => 6]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testInvalidPayloadReturnsUnprocessableEntity(): void
    {
        $this->post(['productId' => 0, 'quantity' => 0]);

        self::assertResponseStatusCodeSame(422);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function post(array $payload, string $csrfToken = self::CSRF_TOKEN): array
    {
        $this->client->request('POST', '/cart/add', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_CSRF_TOKEN' => $csrfToken,
        ], content: json_encode($payload, JSON_THROW_ON_ERROR));

        return json_decode((string) $this->client->getResponse()->getContent(), true) ?? [];
    }
}
