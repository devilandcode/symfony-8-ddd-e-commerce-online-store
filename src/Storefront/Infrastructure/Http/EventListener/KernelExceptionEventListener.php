<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\EventListener;

use App\Catalog\Application\Category\FrontUseCases\Exception\CategoryNotFoundException;
use App\ContentManagement\Application\Page\FrontUseCases\Exception\PageNotFoundException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class KernelExceptionEventListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof PageNotFoundException) {
            $event->setThrowable($this->notFoundHttpResponse('Page not found.', $exception));
        } elseif ($exception instanceof CategoryNotFoundException) {
            $event->setThrowable($this->notFoundHttpResponse('Category not found.', $exception));
        }
    }

    public function notFoundHttpResponse(string $message = 'Not Found', ?\Throwable $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }
}
