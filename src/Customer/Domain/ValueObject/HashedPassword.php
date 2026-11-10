<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class HashedPassword
{
    public function __construct(
        #[ORM\Column(name: 'password', type: 'string', length: 255, nullable: false)]
        private string $password,
    ) {
        if (empty($password)) {
            throw new \InvalidArgumentException('A password hash cannot be empty.');
        }
    }

    public function getHashedPassword(): string
    {
        return $this->password;
    }
}
