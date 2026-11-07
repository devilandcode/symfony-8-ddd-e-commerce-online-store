<?php

namespace App\Customer\Application\CreateUser;

use App\Customer\Domain\Enum\UserRoleEnum;
use App\Customer\Domain\Enum\UserStatusEnum;
use App\Customer\Domain\ValueObject\Email;
use App\Customer\Domain\ValueObject\Name;

final readonly class CreateUserDTO
{
    public function __construct(
        public Name $name,
        public ?string $surname,
        public Email $email,
        public string $plainPassword,
        public UserRoleEnum $role,
        public ?UserStatusEnum $status,
    ) {}
}
