<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Application\FlusherInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineFlusher implements FlusherInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function commit(): void
    {
        $this->entityManager->flush();
    }
}
