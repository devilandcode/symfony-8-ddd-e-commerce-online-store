<?php

namespace App\Customer\Application\CreateUser;

use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;

final readonly class CreateUser
{
    public function __construct(
        public string $name,
        public ?string $surname,
        public string $email,
        public string $plainPassword,
        public UserRoleEnum $role,
        public ?UserStatusEnum $status,
    ) {}
}
