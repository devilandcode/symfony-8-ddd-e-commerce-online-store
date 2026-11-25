<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

/** Maps the query string and answers 400 (instead of the default 404) when it is invalid. */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapCatalogQuery extends MapQueryString
{
    public function __construct()
    {
        parent::__construct(validationFailedStatusCode: Response::HTTP_BAD_REQUEST);
    }
}
