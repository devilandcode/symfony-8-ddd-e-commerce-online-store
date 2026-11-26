<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Controller;

use App\Cart\Application\FrontUseCases\AddToCart\AddToCartHandler;
use App\Cart\Application\FrontUseCases\GetCart\GetCartHandler;
use App\Storefront\Infrastructure\Http\Request\AddToCartParameters;
use App\Storefront\Infrastructure\Http\Request\MapAddToCartPayload;
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
        private readonly GetCartHandler $getCart,
    ) {
    }

    #[Route('/cart', name: 'cart.index', methods: ['GET'])]
    public function getIndex(): Response
    {
        return $this->render('cart.html.twig', [
            'cart' => ($this->getCart)(),
        ]);
    }

    #[Route('/cart/add', name: 'cart.add', methods: ['POST'])]
    #[IsCsrfTokenValid(self::CSRF_TOKEN_ID, tokenKey: self::CSRF_HEADER, methods: 'POST', tokenSource: IsCsrfTokenValid::SOURCE_HEADER)]
    public function addToCart(#[MapAddToCartPayload] AddToCartParameters $parameters): JsonResponse
    {
        $summary = ($this->addToCart)($parameters->toDto());

        return $this->json([
            'success' => true,
            'cartCount' => $summary->itemsCount,
            'cartUrl' => $this->generateUrl('cart.index'),
        ]);
    }
}
