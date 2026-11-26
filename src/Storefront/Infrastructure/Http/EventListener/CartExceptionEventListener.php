<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\EventListener;

use App\Cart\Application\FrontUseCases\Exception\ProductNotFoundException;
use App\Cart\Application\FrontUseCases\Exception\ProductNotPurchasableException;
use App\Cart\Domain\Exception\InvalidCartQuantityException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

/**
 * Translates add-to-cart failures (business, CSRF and payload validation) into the JSON error contract.
 *
 * Registered with a priority above the security firewall listener (1), which would otherwise turn the
 * authentication-type CSRF exception into a redirect to the login page.
 */
final class CartExceptionEventListener
{
    private const string ADD_TO_CART_ROUTE = 'cart.add';

    /** @var array<class-string<\Throwable>, int> */
    private const array BUSINESS_EXCEPTION_STATUS = [
        ProductNotFoundException::class => Response::HTTP_NOT_FOUND,
        ProductNotPurchasableException::class => Response::HTTP_CONFLICT,
        InvalidCartQuantityException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
    ];

    public function onKernelException(ExceptionEvent $event): void
    {
        if (self::ADD_TO_CART_ROUTE !== $event->getRequest()->attributes->get('_route')) {
            return;
        }

        $exception = $event->getThrowable();

        foreach (self::BUSINESS_EXCEPTION_STATUS as $class => $status) {
            if ($exception instanceof $class) {
                $event->setResponse($this->failure($exception->getMessage(), $status));

                return;
            }
        }

        if ($exception instanceof InvalidCsrfTokenException) {
            $event->setResponse($this->failure('Invalid CSRF token.', Response::HTTP_FORBIDDEN));

            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $event->setResponse($this->failure(Response::$statusTexts[$status] ?? 'Error', $status));
        }
    }

    private function failure(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['success' => false, 'message' => $message], $status);
    }
}
