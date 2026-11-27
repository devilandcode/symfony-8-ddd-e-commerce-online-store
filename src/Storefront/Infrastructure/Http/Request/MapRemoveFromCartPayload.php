<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

/** Maps the cart remove JSON body and answers 422 when it is malformed or invalid. */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapRemoveFromCartPayload extends MapRequestPayload
{
    public function __construct()
    {
        parent::__construct(
            acceptFormat: 'json',
            validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
        );
    }
}
