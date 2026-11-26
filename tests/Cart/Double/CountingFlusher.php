<?php

declare(strict_types=1);

namespace App\Tests\Cart\Double;

use App\Shared\Application\FlusherInterface;

final class CountingFlusher implements FlusherInterface
{
    public int $commits = 0;

    public function commit(): void
    {
        ++$this->commits;
    }
}
