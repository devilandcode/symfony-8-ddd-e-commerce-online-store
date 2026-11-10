<?php

declare(strict_types=1);

namespace App\Customer\Application\Output;

use App\Customer\Domain\Entity\User;
use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;

final readonly class UserViewDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $surname,
        public string $email,
        public UserRoleEnum $role,
        public ?UserStatusEnum $status,
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            $user->getId() ?? throw new \LogicException('A user view requires a persisted user.'),
            $user->getName()->getName(),
            $user->getSurname()->getSurname(),
            $user->getEmail()->getEmail(),
            $user->getRole(),
            $user->getStatus(),
        );
    }
}
