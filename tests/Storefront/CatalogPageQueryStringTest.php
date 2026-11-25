<?php

declare(strict_types=1);

namespace App\Tests\Storefront;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CatalogPageQueryStringTest extends WebTestCase
{
    public function testInvalidQueryParametersReturnBadRequestBeforeTheUseCaseRuns(): void
    {
        $client = static::createClient();

        $client->request('GET', '/catalog?page=0');

        self::assertResponseStatusCodeSame(400);
    }

    public function testInvalidFilterValuesReturnBadRequestBeforeTheUseCaseRuns(): void
    {
        $client = static::createClient();

        $client->request('GET', '/catalog?brands%5B%5D=invalid');

        self::assertResponseStatusCodeSame(400);
    }

    public function testInvalidCharacteristicIdsReturnBadRequest(): void
    {
        $client = static::createClient();

        $client->request('GET', '/catalog/face?characteristics%5Bnope%5D%5B%5D=x');

        self::assertResponseStatusCodeSame(400);
    }
}
