<?php

namespace App\Shared\Domain\Exception;

interface HttpCompliantExceptionInterface
{
    public function getHttpCode(): int;

    public function getHttpResponseBody(): string;
}
