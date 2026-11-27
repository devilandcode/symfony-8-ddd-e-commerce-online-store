<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Controller;

use App\Cart\Application\FrontUseCases\AddToCart\AddToCartHandler;
use App\Cart\Application\FrontUseCases\RemoveFromCart\RemoveFromCartHandler;
use App\Cart\Application\FrontUseCases\UpdateCartItemQuantity\UpdateCartItemQuantityHandler;
use App\Storefront\Application\CartPage\Handler\GetCartPageHandler;
use App\Storefront\Infrastructure\Http\Presenter\CartChangePresenter;
use App\Storefront\Infrastructure\Http\Request\AddToCartParameters;
use App\Storefront\Infrastructure\Http\Request\MapAddToCartPayload;
use App\Storefront\Infrastructure\Http\Request\MapRemoveFromCartPayload;
use App\Storefront\Infrastructure\Http\Request\MapUpdateCartItemPayload;
use App\Storefront\Infrastructure\Http\Request\RemoveFromCartParameters;
use App\Storefront\Infrastructure\Http\Request\UpdateCartItemParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

final class CartController extends AbstractController
{
    public const string CSRF_TOKEN_ID = 'app';
    public const string CSRF_HEADER = 'X-CSRF-Token';

    public function __construct(
        private readonly AddToCartHandler $addToCart,
        private readonly UpdateCartItemQuantityHandler $updateCartItemQuantity,
        private readonly RemoveFromCartHandler $removeFromCart,
        private readonly GetCartPageHandler $getCartPage,
        private readonly CartChangePresenter $presenter,
    ) {
    }

    #[Route('/cart', name: 'cart.index', methods: ['GET'])]
    public function getIndex(): Response
    {
        $cartPage = ($this->getCartPage)();

        return $this->render('cart.html.twig', compact('cartPage'));
    }

    #[Route('/cart/add', name: 'cart.add', methods: ['POST'])]
    #[IsCsrfTokenValid(self::CSRF_TOKEN_ID, tokenKey: self::CSRF_HEADER, methods: 'POST', tokenSource: IsCsrfTokenValid::SOURCE_HEADER)]
    public function addToCart(#[MapAddToCartPayload] AddToCartParameters $parameters): JsonResponse
    {
        $summary = ($this->addToCart)($parameters->toDto());

        return $this->json([
            'success' => true,
            'cartCount' => $summary->totalQuantity,
            'cartUrl' => $this->generateUrl('cart.index'),
        ]);
    }

    #[Route('/cart/update', name: 'cart.update', methods: ['PUT'])]
    #[IsCsrfTokenValid(self::CSRF_TOKEN_ID, tokenKey: self::CSRF_HEADER, methods: 'PUT', tokenSource: IsCsrfTokenValid::SOURCE_HEADER)]
    public function updateCart(#[MapUpdateCartItemPayload] UpdateCartItemParameters $parameters): JsonResponse
    {
        $result = ($this->updateCartItemQuantity)($parameters->toDto());

        return $this->json($this->presenter->presentUpdate($result, $parameters->productId));
    }

    #[Route('/cart/remove', name: 'cart.remove', methods: ['DELETE'])]
    #[IsCsrfTokenValid(self::CSRF_TOKEN_ID, tokenKey: self::CSRF_HEADER, methods: 'DELETE', tokenSource: IsCsrfTokenValid::SOURCE_HEADER)]
    public function removeCart(#[MapRemoveFromCartPayload] RemoveFromCartParameters $parameters): JsonResponse
    {
        $result = ($this->removeFromCart)($parameters->toDto());

        return $this->json($this->presenter->presentRemoval($result, $parameters->productId));
    }
}
